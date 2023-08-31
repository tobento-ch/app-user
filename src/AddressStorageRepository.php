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

use Tobento\Service\Storage\StorageInterface;
use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\Column\ColumnsInterface;
use Tobento\Service\Repository\Storage\Column\ColumnInterface;
use Tobento\Service\Repository\Storage\Column;
use Tobento\Service\Storage\ItemsInterface;
use Tobento\Service\Storage\Items;
use Tobento\Service\Storage\ItemInterface;
use Tobento\Service\User\AddressesInterface;
use Tobento\Service\User\AddressInterface;

/**
 * AddressStorageRepository
 */
class AddressStorageRepository extends StorageRepository implements AddressRepositoryInterface
{
    /**
     * Create a new StorageRepository.
     *
     * @param StorageInterface $storage
     * @param string $table
     * @param AddressFactoryInterface $addressFactory
     * @param null|iterable<ColumnInterface>|ColumnsInterface $columns
     */
    public function __construct(
        protected StorageInterface $storage,
        protected string $table,
        protected AddressFactoryInterface $addressFactory,
        null|iterable|ColumnsInterface $columns = null,
    ) {
        $this->columns = $this->processColumns($columns);
        $addressFactory->setColumns($this->columns);
        $this->entityFactory = $addressFactory;
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
            Column\Text::new('key'),
            Column\Integer::new('user_id'),
            Column\Text::new('group'),
            Column\Boolean::new('default_address'),
            Column\Text::new('salutation'),
            Column\Text::new('name'),
            Column\Text::new('firstname'),
            Column\Text::new('lastname'),
            Column\Text::new('company'),
            Column\Text::new('address1'),
            Column\Text::new('address2'),
            Column\Text::new('address3'),
            Column\Text::new('postcode')->type(length: 10),
            Column\Text::new('city'),
            Column\Text::new('state'),
            Column\Text::new('country_key', type: 'char')->type(length: 5),
            Column\Text::new('country'),
            Column\Text::new('email'),
            Column\Text::new('telephone')->type(length: 15),
            Column\Text::new('smartphone')->type(length: 15),
            Column\Text::new('fax')->type(length: 15),
            Column\Text::new('website'),
            Column\Text::new('locale', type: 'char')->type(length: 5),
            Column\Datetime::new('birthday', type: 'date'),
            Column\Text::new('notice', type: 'text'),
            Column\Text::new('info', type: 'text'),
            Column\Boolean::new('selectable'),
        ];
    }
    
    /**
     * Returns the found address for the user id or null if none found.
     *
     * @param int|string $userId
     * @return null|AddressInterface
     */
    public function findOneByUserId(int|string $userId): null|AddressInterface
    {
        return $this->findOne(where: ['user_id' => $userId]);
    }
    
    /**
     * Returns the found addresses for the user id.
     *
     * @param int|string $userId
     * @return AddressesInterface
     */
    public function findAllByUserId(int|string $userId): AddressesInterface
    {
        return $this->addressFactory->createAddresses(
            addresses: $this->findAll(where: ['user_id' => $userId])
        );
    }
    
    /**
     * Returns the found default addresses for the user id.
     *
     * @param int|string $userId
     * @return AddressesInterface
     */
    public function findAllDefaultByUserId(int|string $userId): AddressesInterface
    {
        return $this->addressFactory->createAddresses(
            addresses: $this->findAll(where: ['user_id' => $userId, 'default_address' => true])
        );
    }

    /**
     * Returns all default addresses for the user ids grouped by user id.
     *
     * @param array $userIds
     * @return ItemsInterface
     */
    public function findAllByUserIdsGrouped(array $userIds): ItemsInterface
    {
        $items = parent::findAll(where: [
            'user_id' => ['in' => $userIds],
        ]);
        
        if (! $items instanceof ItemsInterface) {
            $items = new Items($items);
        }
        
        return $items->groupBy(
            groupBy: fn (AddressInterface $address): int => $address->userId(),
            groupAs: fn (array $group): AddressesInterface => $this->addressFactory->createAddresses($group),
        );
    }
    
    /**
     * Returns all default addresses for the user ids grouped by user id.
     *
     * @param array $userIds
     * @return ItemsInterface
     */
    public function findAllDefaultByUserIdsGrouped(array $userIds): ItemsInterface
    {
        $items = parent::findAll(where: [
            'user_id' => ['in' => $userIds],
            'default_address' => true,
        ]);
        
        if (! $items instanceof ItemsInterface) {
            $items = new Items($items);
        }
        
        return $items->groupBy(
            groupBy: fn (AddressInterface $address): int => $address->userId(),
            groupAs: fn (array $group): AddressesInterface => $this->addressFactory->createAddresses($group),
        );
    }
}