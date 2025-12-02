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
            new Column\Id(),
            new Column\Text('key'),
            new Column\Integer('user_id'),
            new Column\Text('group'),
            new Column\Text('salutation'),
            new Column\Text('name'),
            new Column\Text('firstname'),
            new Column\Text('lastname'),
            new Column\Text('company'),
            new Column\Text('address1'),
            new Column\Text('address2'),
            new Column\Text('address3'),
            new Column\Text('postcode')->type(length: 10),
            new Column\Text('city'),
            new Column\Text('state'),
            new Column\Text(name: 'country_key', type: 'char')->type(length: 5),
            new Column\Text('country'),
            new Column\Text('email'),
            new Column\Text('telephone')->type(length: 15),
            new Column\Text('smartphone')->type(length: 15),
            new Column\Text('fax')->type(length: 15),
            new Column\Text('website'),
            new Column\Text(name: 'locale', type: 'char')->type(length: 5),
            new Column\Datetime(name: 'birthday', type: 'date'),
            new Column\Text(name: 'notice', type: 'text'),
            new Column\Text(name: 'info', type: 'text'),
            new Column\Boolean('selectable'),
            new Column\Json('meta'),
        ];
    }
    
    /**
     * Returns the found primary address for the user id or null if none found.
     *
     * @param int|string $userId
     * @return null|AddressInterface
     */
    public function findPrimaryByUserId(int|string $userId): null|AddressInterface
    {
        return $this->findOne(where: ['user_id' => $userId, 'key' => 'primary']);
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
     * Returns all addresses for the user ids grouped by user id.
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
     * Returns all primary addresses for the user ids grouped by user id.
     *
     * @param array $userIds
     * @return ItemsInterface
     */
    public function findAllPrimaryByUserIdsGrouped(array $userIds): ItemsInterface
    {
        $items = parent::findAll(where: [
            'user_id' => ['in' => $userIds],
            'key' => 'primary',
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