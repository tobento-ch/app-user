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

namespace Tobento\App\User\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Test\Factory;
use Tobento\App\User\AddressStorageRepository;
use Tobento\App\User\AddressRepositoryInterface;
use Tobento\Service\Storage\InMemoryStorage;
    
class AddressStorageRepositoryTest extends TestCase
{    
    public function testThatImplementsAddressRepositoryInterface()
    {
        $this->assertInstanceOf(
            AddressRepositoryInterface::class,
            new AddressStorageRepository(
                storage: new InMemoryStorage([]),
                table: 'addresses',
                addressFactory: Factory::createAddressFactory(),
            )
        );
    }
    
    public function testFindPrimaryByUserIdMethod()
    {
        $repo = new AddressStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'addresses',
            addressFactory: Factory::createAddressFactory(),
        );
        
        $repo->create(['user_id' => 5, 'key' => 'primary', 'firstname' => 'Tom']);
        
        $this->assertNull($repo->findPrimaryByUserId(2));
        $this->assertSame('Tom', $repo->findPrimaryByUserId(5)?->firstname());
    }
    
    public function testFindAllByUserIdMethod()
    {
        $repo = new AddressStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'addresses',
            addressFactory: Factory::createAddressFactory(),
        );
        
        $repo->create(['user_id' => 5, 'firstname' => 'Tom']);
        $repo->create(['user_id' => 5, 'firstname' => 'John']);
        
        $this->assertSame(0, count($repo->findAllByUserId(2)->all()));
        $this->assertSame(2, count($repo->findAllByUserId(5)->all()));
    }
    
    public function testFindAllByUserIdsGroupedMethod()
    {
        $repo = new AddressStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'addresses',
            addressFactory: Factory::createAddressFactory(),
        );
        
        $repo->create(['user_id' => 5, 'firstname' => 'Tom']);
        $repo->create(['user_id' => 5, 'firstname' => 'John']);
        $repo->create(['user_id' => 7, 'firstname' => 'James']);
        $repo->create(['user_id' => 8, 'firstname' => 'Hannes']);
        
        $items = $repo->findAllByUserIdsGrouped([5,7]);
        
        $this->assertSame(2, count($items->get(5)->all()));
        $this->assertSame(1, count($items->get(7)->all()));
        $this->assertSame([5,7], array_keys($items->all()));
    }
    
    public function testFindAllPrimaryByUserIdsGroupedMethod()
    {
        $repo = new AddressStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'addresses',
            addressFactory: Factory::createAddressFactory(),
        );
        
        $repo->create(['user_id' => 3, 'firstname' => 'Tom']);
        $repo->create(['user_id' => 5, 'firstname' => 'Tom', 'key' => 'primary']);
        $repo->create(['user_id' => 5, 'firstname' => 'Max']);
        $repo->create(['user_id' => 7, 'firstname' => 'James', 'key' => 'primary']);
        $repo->create(['user_id' => 8, 'firstname' => 'Hannes', 'key' => 'primary']);
        
        $items = $repo->findAllPrimaryByUserIdsGrouped([5,7]);
        
        $this->assertSame(1, count($items->get(5)->all()));
        $this->assertSame(1, count($items->get(7)->all()));
        $this->assertSame([5,7], array_keys($items->all()));
    }    
    
    public function testCountryIsLocalized()
    {
        $repo = new AddressStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'addresses',
            addressFactory: Factory::createAddressFactory(locale: 'en'),
        );
        
        $address = $repo->create(['user_id' => 5, 'key' => 'primary', 'country_key' => 'CH', 'country' => 'Schweiz']);
        
        $this->assertSame('Switzerland', $repo->findPrimaryByUserId(5)->country());
    }    
}