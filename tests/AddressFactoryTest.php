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
use Tobento\App\User\AddressFactory;
use Tobento\App\User\AddressFactoryInterface;
use Tobento\Service\User\AddressesFactory;
use Tobento\Service\User\AddressFactory as ServiceAddressFactory;
use Tobento\Service\User\AddressInterface;
use Tobento\Service\User\Address;
use Tobento\Service\Storage\Item;
use Tobento\Service\Country\CountryRepository;
    
class AddressFactoryTest extends TestCase
{    
    public function testThatImplementsAddressFactoryInterface()
    {
        $this->assertInstanceOf(
            AddressFactoryInterface::class,
            new AddressFactory(
                addressFactory: new ServiceAddressFactory(),
                addressesFactory: new AddressesFactory(new ServiceAddressFactory()),
            )
        );
    }
    
    public function testCreateEntityFromArrayMethod()
    {
        $addressFactory = new AddressFactory(
            addressFactory: new ServiceAddressFactory(),
            addressesFactory: new AddressesFactory(new ServiceAddressFactory()),
        );
        
        $address = $addressFactory->createEntityFromArray(['key' => 'payment']);
        
        $this->assertSame('payment', $address->key());
    }
    
    public function testCreateAddressesMethodWithArrayAddresses()
    {
        $addressFactory = new AddressFactory(
            addressFactory: new ServiceAddressFactory(),
            addressesFactory: new AddressesFactory(new ServiceAddressFactory()),
        );
        
        $addresses = $addressFactory->createAddresses([
            ['key' => 'payment', 'firstname' => 'tom'],
        ]);
        
        $this->assertTrue($addresses->has('payment'));
    }
    
    public function testCreateAddressesMethodAddresses()
    {
        $addressesFactory = new AddressesFactory(new ServiceAddressFactory());
        
        $addressFactory = new AddressFactory(
            addressFactory: new ServiceAddressFactory(),
            addressesFactory: $addressesFactory,
        );
        
        $ads = $addressesFactory->createAddressesFromArray([
            ['key' => 'payment', 'firstname' => 'tom'],
        ]);
        
        $addresses = $addressFactory->createAddresses($ads);
        
        $this->assertSame($ads, $addresses);
    }
    
    public function testCreateAddressesMethodWithItemAddresses()
    {
        $addressFactory = new AddressFactory(
            addressFactory: new ServiceAddressFactory(),
            addressesFactory: new AddressesFactory(new ServiceAddressFactory()),
        );
        
        $addresses = $addressFactory->createAddresses([
            new Item(['key' => 'payment', 'firstname' => 'tom']),
        ]);
        
        $this->assertTrue($addresses->has('payment'));
    }
    
    public function testCreateAddressesMethodWithAddressAddresses()
    {
        $addressFactory = new AddressFactory(
            addressFactory: new ServiceAddressFactory(),
            addressesFactory: new AddressesFactory(new ServiceAddressFactory()),
        );
        
        $addresses = $addressFactory->createAddresses([
            new Address(key: 'payment'),
        ]);
        
        $this->assertTrue($addresses->has('payment'));
    }
    
    public function testCountryIsAdded()
    {
        $addressFactory = new AddressFactory(
            addressFactory: new ServiceAddressFactory(),
            addressesFactory: new AddressesFactory(new ServiceAddressFactory()),
            countryRepository: new CountryRepository(),
        );
        
        $address = $addressFactory->createEntityFromArray(['key' => 'payment', 'country_key' => 'CH']);
        
        $this->assertSame('Switzerland', $address->country());
    }
    
    public function testCountryIsNotAddedIfNotCountryKey()
    {
        $addressFactory = new AddressFactory(
            addressFactory: new ServiceAddressFactory(),
            addressesFactory: new AddressesFactory(new ServiceAddressFactory()),
            countryRepository: new CountryRepository(),
        );
        
        $address = $addressFactory->createEntityFromArray(['key' => 'payment']);
        
        $this->assertSame('', $address->country());
    }
}