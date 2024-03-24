<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\User;

use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\Column\ColumnsInterface;
use Tobento\Service\Repository\Storage\Column\ColumnInterface;
use Tobento\Service\Repository\Storage\Column;
use Tobento\Service\Repository\RepositoryReadException;
use Tobento\Service\Repository\RepositoryCreateException;
use Tobento\Service\Repository\RepositoryUpdateException;
use Tobento\Service\Repository\RepositoryDeleteException;
use Tobento\Service\Storage\StorageInterface;
use DateTimeInterface;
use Throwable;

/**
 * UserStorageRepository
 */
class UserStorageRepository extends StorageRepository implements UserRepositoryInterface
{
    /**
     * Create a new UserStorageRepository.
     *
     * @param StorageInterface $storage
     * @param string $table
     * @param UserFactoryInterface $userFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param null|iterable<ColumnInterface>|ColumnsInterface $columns
     */
    public function __construct(
        protected StorageInterface $storage,
        protected string $table,
        UserFactoryInterface $userFactory,
        protected AddressRepositoryInterface $addressRepository,
        null|iterable|ColumnsInterface $columns = null,
    ) {
        $this->columns = $this->processColumns($columns);
        $this->entityFactory = $userFactory;
        $this->entityFactory->setColumns($this->columns);
    }
    
    /**
     * Returns the configured columns.
     *
     * @return iterable<ColumnInterface>|ColumnsInterface
     */
    protected function configureColumns(): iterable|ColumnsInterface
    {
        return [
            Column\Id::new(),
            Column\Text::new('role_key'),
            Column\Text::new('number'),
            Column\Boolean::new('active'),
            Column\Text::new('type'),
            Column\Text::new('password'),
            Column\Text::new('username'),
            Column\Text::new('email'),
            Column\Text::new('smartphone')->type(length: 15),
            Column\Text::new('locale', type: 'char')->type(length: 5),
            Column\Datetime::new('birthday', type: 'date'),
            Column\Datetime::new('date_created'),
            Column\Datetime::new('date_updated'),
            Column\Datetime::new('date_last_visited'),
            Column\Json::new('image'),
            Column\Boolean::new('newsletter'),
            Column\Json::new('permissions'),
            Column\Json::new('verified'),
            Column\Json::new('settings'),
        ];
    }
    
    /**
     * Returns the found entity using the specified where parameters
     * or null if none found.
     *
     * @param array $where
     * @return null|UserInterface
     * @throws RepositoryReadException
     */
    public function findOne(array $where = []): null|UserInterface
    {
        return parent::findOne(where: $where);
    }
    
    /**
     * Returns the found entity using the specified id (primary key)
     * or null if none found.
     *
     * @param int|string $id
     * @return null|UserInterface
     * @throws RepositoryReadException
     */
    public function findById(int|string $id): null|UserInterface
    {
        return parent::findById(id: $id);
    }
    
    /**
     * Returns the found user using the specified unique identity parameters
     * usually used for login or null if none found.
     *
     * @param string $email
     * @param string $username
     * @param string $smartphone
     * @return null|UserInterface
     */
    public function findByIdentity(string $email = '', string $username = '', string $smartphone = ''): null|UserInterface
    {
        $query = $this->query();
        
        if ($email === '' && $username === '' && $smartphone === '') {
            return null;
        }
        
        if ($email !== '') {
            $query->orWhere('email', '=', $email);
        }
        
        if ($username !== '') {
            $query->orWhere('username', '=', $username);
        }
        
        if ($smartphone !== '') {
            $query->orWhere('smartphone', '=', $smartphone);
        }
        
        return $this->createEntityOrNull(
            item: $query->first(),
        );
    }
    
    /**
     * Create a new user with primary address.
     *
     * @param array $user
     * @param array $address
     * @return UserInterface
     * @throws RepositoryCreateException
     */
    public function createWithAddress(array $user, array $address = []): UserInterface
    {
        if (!empty($address)) {
            return $this->createWithPrimaryAddress($user, $address);
        }
        
        return $this->create($user);
    }
    
    /**
     * Update a user and its primary address.
     *
     * @param string|int $id The user id.
     * @param array $user
     * @param array $address
     * @return UserInterface
     * @throws RepositoryUpdateException
     * @throws RepositoryCreateException
     */
    public function updateWithAddress(string|int $id, array $user, array $address = []): UserInterface
    {
        $user = $this->updateById($id, $user);
        
        if (empty($address)) {
            return $user;
        }
            
        $primaryAddress = $this->addressRepository->findPrimaryByUserId($id);

        if (is_null($primaryAddress)) {
            // create address:
            $address['user_id'] = $id;
            $address['key'] = 'primary';
            
            $address = $this->addressRepository->create($address);
            
            $user->addresses()->add($address);
            return $user;
        }
        
        // update address:
        unset($address['user_id']);
        unset($address['key']);
        
        $address = $this->addressRepository->updateById(
            id: $primaryAddress->id(),
            attributes: $address,
        );

        $user->addresses()->add($address);
        
        return $user;
    }
    
    /**
     * Delete an user with its addresses.
     *
     * @param string|int $id The user id.
     * @return UserInterface
     * @throws RepositoryDeleteException
     */
    public function deleteWithAddresses(string|int $id): UserInterface
    {
        $addresses = $this->addressRepository->delete(where: ['user_id' => $id]);
        
        $user = $this->deleteById($id);
        
        $user->addresses()->add(...$addresses);
        
        return $user;
    }
    
    /**
     * Create an user with primary address.
     *
     * @param array $user
     * @param array $address
     * @return UserInterface
     * @throws RepositoryCreateException
     */
    protected function createWithPrimaryAddress(array $user, array $address): UserInterface
    {
        $this->storage()->begin();

        try {
            $createUser = $this->create($user);

            $address['user_id'] = $createUser->id();
            $address['key'] = 'primary';
            
            $createAddress = $this->addressRepository->create($address);

            $createUser->addresses()->add($createAddress);
            
            $this->storage()->commit();
            
            return $createUser;
            
        } catch (Throwable $e) {
            
            $this->storage()->rollback();

            $user['address'] = $address;
            throw new RepositoryCreateException($user, $e->getMessage(), (int)$e->getCode(), $e);
        }
    }
    
    /**
     * Add a verified channel.
     *
     * @param string|int $id The user id.
     * @param string $channel
     * @param DateTimeInterface $verifiedAt
     * @return UserInterface
     * @throws RepositoryUpdateException
     */
    public function addVerified(string|int $id, string $channel, DateTimeInterface $verifiedAt): UserInterface
    {
        $user = $this->findById($id);
        
        if (is_null($user)) {
            throw new RepositoryUpdateException(
                message: sprintf('User with the id %s not found', (string) $id)
            );
        }
        
        $verified = $user->getVerified();
        $verified[$channel] = $verifiedAt->format('Y-m-d H:i:s');
        
        return $this->updateById(
            id: $id,
            attributes: ['verified' => $verified],
        );
    }
    
    /**
     * Remove a verified channel.
     *
     * @param string|int $id The user id.
     * @param string $channel
     * @return UserInterface
     * @throws RepositoryUpdateException
     */
    public function removeVerified(string|int $id, string $channel): UserInterface
    {
        $user = $this->findById($id);
        
        if (is_null($user)) {
            throw new RepositoryUpdateException(
                message: sprintf('User with the id %s not found', (string) $id)
            );
        }
        
        $verified = $user->getVerified();
        unset($verified[$channel]);
        
        return $this->updateById(
            id: $id,
            attributes: ['verified' => $verified],
        );
    }
}