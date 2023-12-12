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

use Tobento\Service\Repository\Storage\EntityFactory;
use Tobento\Service\Storage\ItemsInterface;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Collection\Collection;

/**
 * UserFactory
 */
class UserFactory extends EntityFactory implements UserFactoryInterface
{
    /**
     * Create a new UserFactory.
     *
     * @param AclInterface $acl
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        protected AclInterface $acl,
        protected AddressRepositoryInterface $addressRepository,
        protected AddressFactoryInterface $addressFactory,
    ) {
        parent::__construct(null);
    }
    
    /**
     * Create an entity from array.
     *
     * @param array $attributes
     * @return UserInterface The created entity.
     */
    public function createEntityFromArray(array $attributes): UserInterface
    {
        $data = new Collection($this->columns->processReading($attributes));
        
        // Addresses:
        if ($data->has('addresses')) {
            $addresses = $this->addressFactory->createAddresses($data->get('addresses'));
        } else {
            // load primary address:
            if ($data->has('address')) {
                $address = $data->get('address', []);
                $address['key'] = 'primary';
                $address['user_id'] ??= $data->get('id', 0);
                $address = [$address];
            } else {
                $address = $this->addressRepository->findPrimaryByUserId($data->get('id', 0));
            }
            
            $addresses = $this->addressFactory->createAddresses($address);
        }
        
        // Create user:
        $user = new User(
            id: $data->get('id', 0),
            number: $data->get('number', ''),
            active: $data->get('active', true),
            type: $data->get('type', ''),
            password: $data->get('password', ''),
            username: $data->get('username', ''),
            email: $data->get('email', ''),
            smartphone: $data->get('smartphone', ''),
            locale: $data->get('locale', ''),
            birthday: $data->get('birthday', ''),
            dateCreated: $data->get('date_created', ''),
            dateUpdated: $data->get('date_updated', ''),
            dateLastVisited: $data->get('date_last_visited', ''),
            image: $data->get('image', []),
            newsletter: $data->get('newsletter', false),
            addresses: $addresses,
        );
        
        // Get specific role:
        $role = $this->acl->getRole($data->get('role_key', 'guest'));

        // Check if role exists:
        if (is_null($role)) {
            $role = $this->acl->getRole('guest');
        }
        
        if (is_null($role)) {
            return $user;
        }
        
        $user->setRole($role);
        $user->setRoleKey($role->key());
        
        // Specific user permissions:
        // only add permissions if there are any,
        // otherwise role permissions are used instead.
        if ($data->has('permissions') && ! $data->empty('permissions')) {
            $user->addPermissions($data->get('permissions', []));
        }
        
        // Verified:
        $user->setVerified($data->get('verified', []));
        
        return $user;
    }
    
    /**
     * Create entities from storage items.
     *
     * @param ItemsInterface $items
     * @return iterable<object> The created entities.
     */
    public function createEntitiesFromStorageItems(ItemsInterface $items): iterable
    {
        $addresses = $this->addressRepository->findAllPrimaryByUserIdsGrouped($items->column('id'));
        
        return $items->map(function(array $item) use ($addresses): UserInterface {
            $item['addresses'] = $addresses->get($item['id'] ?? 0, []);
            return $this->createEntityFromArray($item);
        });
    }
    
    /**
     * Create guest user.
     *
     * @return UserInterface
     */
    public function createGuestUser(): UserInterface
    {
        return $this->createEntityFromArray(['role_key' => 'guest']);
    }
}