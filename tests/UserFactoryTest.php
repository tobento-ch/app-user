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
use Tobento\App\User\UserFactory;
use Tobento\App\User\UserFactoryInterface;
use Tobento\Service\Storage\Items;
    
class UserFactoryTest extends TestCase
{    
    public function testThatImplementsUserFactoryInterface()
    {
        $this->assertInstanceOf(
            UserFactoryInterface::class,
            new UserFactory(
                acl: Factory::createAcl(),
                addressRepository: Factory::createAddressRepository(),
                addressFactory: Factory::createAddressFactory(),
            )
        );
    }
    
    public function testCreateEntityFromArrayMethod()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository(),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createEntityFromArray([
            'id' => 2,
            'number' => 'NUM',
            'active' => true,
            'password' => 'hashedPw',
            'username' => 'Username',
            'email' => 'tom@example.com',
            'smartphone' => '555-55-333',
            'locale' => 'de',
        ]);
        
        $this->assertSame(2, $user->id());
        $this->assertSame('NUM', $user->number());
        $this->assertTrue($user->active());
        $this->assertSame('hashedPw', $user->password());
        $this->assertSame('Username', $user->username());
        $this->assertSame('tom@example.com', $user->email());
        $this->assertSame('555-55-333', $user->smartphone());
        $this->assertSame('de', $user->locale());
    }

    public function testDefinedAddressesAreAssigned()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository(),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createEntityFromArray([
            'id' => 2,
            'email' => 'tom@example.com',
            'addresses' => [
                ['key' => 'primary', 'firstname' => 'Tom'],
                ['key' => 'payment', 'firstname' => 'John']
            ]
        ]);
        
        $this->assertSame('Tom', $user->address()->firstname());
        $this->assertSame('John', $user->address('payment')->firstname());
    }
    
    public function testPrimaryAddressesAreAssignedIfNoneDefined()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository([
                ['user_id' => 2, 'key' => 'primary', 'firstname' => 'Tom'],
            ]),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createEntityFromArray([
            'id' => 2,
            'email' => 'tom@example.com',
        ]);

        $this->assertSame('Tom', $user->address()->firstname());
    }
        
    public function testGuestRoleIsAssignedIfNone()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository(),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createEntityFromArray([
            'id' => 2,
            'email' => 'tom@example.com',
        ]);
        
        $this->assertSame('guest', $user->role()->key());
    }
    
    public function testDefinedRoleIsAssignedIfExists()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository(),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createEntityFromArray([
            'id' => 2,
            'email' => 'tom@example.com',
            'role_key' => 'editor',
        ]);
        
        $this->assertSame('editor', $user->role()->key());
    }
    
    public function testDefinedRoleIsNotAssignedIfNotExistsAndFallsbackToGuest()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository(),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createEntityFromArray([
            'id' => 2,
            'email' => 'tom@example.com',
            'role_key' => 'foo',
        ]);
        
        $this->assertSame('guest', $user->role()->key());
    }
    
    public function testNoRoleIsSetIfNoneExists()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(withDefaultRoles: false),
            addressRepository: Factory::createAddressRepository(),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createEntityFromArray([
            'id' => 2,
            'email' => 'tom@example.com',
            'role_key' => 'foo',
        ]);
        
        $this->assertFalse($user->hasRole());
    }
    
    public function testPermissionsAreSetToUserOnly()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository(),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createEntityFromArray([
            'id' => 2,
            'email' => 'tom@example.com',
            'permissions' => ['article.create'],
        ]);
        
        $this->assertSame(['article.create'], $user->getPermissions());
        $this->assertSame([], $user->role()->getPermissions());
    }
    
    public function testVerifiedAttributeIsSet()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository(),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createEntityFromArray([
            'id' => 2,
            'verified' => ['email' => '2023-09-24 00:00:00'],
        ]);
        
        $this->assertSame(['email' => '2023-09-24 00:00:00'], $user->getVerified());
    }
    
    public function testCreateEntitiesFromStorageItemsMethod()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository([
                ['user_id' => 2, 'key' => 'primary', 'firstname' => 'Tom'],
            ]),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $users = $userFactory->createEntitiesFromStorageItems(new Items([
            ['id' => 2, 'email' => 'tom@example.com'],
        ]));
        
        $this->assertSame(2, $users->first()?->id());
        $this->assertSame('Tom', $users->first()?->address()->firstname());
    }
    
    public function testCreateGuestUserMethod()
    {
        $userFactory = new UserFactory(
            acl: Factory::createAcl(),
            addressRepository: Factory::createAddressRepository(),
            addressFactory: Factory::createAddressFactory(),
        );
        
        $user = $userFactory->createGuestUser();
        
        $this->assertSame(0, $user->id());
        $this->assertSame('guest', $user->role()->key());
    }
}