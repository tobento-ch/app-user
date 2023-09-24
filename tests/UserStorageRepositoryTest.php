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
use Tobento\App\User\UserStorageRepository;
use Tobento\App\User\AddressStorageRepository;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\Service\Repository\Storage\Column;
use DateTime;
use Throwable;
    
class UserStorageRepositoryTest extends TestCase
{    
    public function testThatImplementsUserRepositoryInterface()
    {
        $this->assertInstanceOf(
            UserRepositoryInterface::class,
            new UserStorageRepository(
                storage: new InMemoryStorage([]),
                table: 'users',
                userFactory: Factory::createUserFactory(),
                addressRepository: Factory::createAddressRepository(),
            )
        );
    }
    
    public function testFindOneMethod()
    {
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(),
            addressRepository: Factory::createAddressRepository(),
        );
        
        $this->assertNull($repo->findOne());
        
        $repo->create(['email' => 'tom@example.com']);
        $repo->create(['email' => 'james@example.com']);
        
        $this->assertSame('tom@example.com', $repo->findOne()?->email());
        $this->assertSame('james@example.com', $repo->findOne(where: ['email' => 'james@example.com'])?->email());
    }
    
    public function testFindByIdMethod()
    {
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(),
            addressRepository: Factory::createAddressRepository(),
        );
        
        $this->assertNull($repo->findById(1));
        
        $repo->create(['email' => 'tom@example.com']);
        
        $this->assertSame('tom@example.com', $repo->findById(1)?->email());
        $this->assertNull($repo->findById(2));
    }
    
    public function testFindByIdentityMethod()
    {
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(),
            addressRepository: Factory::createAddressRepository(),
        );
        
        $this->assertNull($repo->findByIdentity(email: 'tom@example.com'));
        
        $repo->create(['id' => 5, 'email' => 'tom@example.com', 'username' => 'TOM', 'smartphone' => '555']);
        
        $this->assertSame(5, $repo->findByIdentity(email: 'tom@example.com')?->id());
        $this->assertSame(5, $repo->findByIdentity(username: 'TOM')?->id());
        $this->assertSame(5, $repo->findByIdentity(smartphone: '555')?->id());
        $this->assertSame(5, $repo->findByIdentity(email: 'tom@example.com', username: 'TOM')?->id());
        $this->assertSame(5, $repo->findByIdentity(email: 'tom@example.com', username: 'TOM', smartphone: '555')?->id());
        $this->assertSame(5, $repo->findByIdentity(smartphone: '555', username: 'TOM')?->id());
        $this->assertSame(5, $repo->findByIdentity(email: 'tom@example.com', username: 'Foo')?->id());
        $this->assertSame(5, $repo->findByIdentity(email: 'tom@example.com', username: 'Foo', smartphone: '333')?->id());
        $this->assertSame(5, $repo->findByIdentity(email: 'foo@example.com', username: 'TOM')?->id());
    }
    
    public function testCreateWithAddressMethod()
    {
        $addressRepo = Factory::createAddressRepository();
        
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(addressRepository: $addressRepo),
            addressRepository: $addressRepo,
        );
        
        $createUser = $repo->createWithAddress(
            user: [
                'id' => 5,
                'email' => 'tom@example.com',
                'smartphone' => '555',
            ],
            address: [
                'firstname' => 'Tom',
                'lastname' => 'Taylor',
            ],
        );
        
        $this->assertSame(5, $createUser->id());
        $this->assertSame('Tom', $createUser->address()->firstname());
        
        $user = $repo->findById(5);
        $this->assertSame(5, $user->id());
        $this->assertSame('Tom', $user->address()->firstname());
    }
    
    public function testCreateWithAddressMethodWithoutAddress()
    {
        $addressRepo = Factory::createAddressRepository();
        
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(addressRepository: $addressRepo),
            addressRepository: $addressRepo,
        );
        
        $createUser = $repo->createWithAddress(
            user: [
                'id' => 5,
                'email' => 'tom@example.com',
                'smartphone' => '555',
            ],
        );
        
        $this->assertSame(5, $createUser->id());
        $this->assertSame('', $createUser->address()->firstname());
        
        $user = $repo->findById(5);
        $this->assertSame(5, $user->id());
        $this->assertSame('', $user->address()->firstname());
        
        $this->assertSame(0, $addressRepo->findAll()->count());
    }    
    
    public function testCreateWithAddressMethodRollsbackOnFailure()
    {
        $addressRepo = new AddressStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'addresses',
            addressFactory: Factory::createAddressFactory(),
            columns: [
                Column\Id::new(),
                Column\Text::new('key'),
                Column\Integer::new('user_id'),
                Column\Text::new('firstname')->write(fn () => throw new \Exception()),
            ],
        );
        
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(addressRepository: $addressRepo),
            addressRepository: $addressRepo,
        );

        try {
            $createUser = $repo->createWithAddress(
                user: [
                    'id' => 5,
                    'email' => 'tom@example.com',
                    'smartphone' => '555',
                ],
                address: [
                    'firstname' => 'Tom',
                    'lastname' => 'Taylor',
                ],
            );
        } catch (Throwable $t) {
            //throw $t;
        }
        
        $this->assertSame(0, $repo->findAll()->count());
        $this->assertSame(0, $addressRepo->findAll()->count());
    }
    
    public function testUpdateWithAddressMethod()
    {
        $addressRepo = Factory::createAddressRepository();
        
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(addressRepository: $addressRepo),
            addressRepository: $addressRepo,
        );
        
        $repo->createWithAddress(
            user: [
                'id' => 5,
                'email' => 'tom@example.com',
                'smartphone' => '555',
            ],
            address: [
                'firstname' => 'Tom',
                'lastname' => 'Taylor',
            ],
        );
        
        $updatedUser = $repo->updateWithAddress(
            id: 5,
            user: [
                'email' => 'tim@example.com',
                'smartphone' => '333',
            ],
            address: [
                'firstname' => 'Tim',
                'lastname' => 'Thomsen',
            ],
        );
        
        $this->assertSame(5, $updatedUser->id());
        $this->assertSame('333', $updatedUser->smartphone());
        $this->assertSame('Tim', $updatedUser->address()->firstname());
        
        $user = $repo->findById(5);
        $this->assertSame(5, $user->id());
        $this->assertSame('333', $user->smartphone());
        $this->assertSame('Tim', $user->address()->firstname());
        
        $this->assertSame(1, $repo->findAll()->count());
        $this->assertSame(1, $addressRepo->findAll()->count());
    }
    
    public function testUpdateWithAddressMethodIfNoAddress()
    {
        $addressRepo = Factory::createAddressRepository();
        
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(addressRepository: $addressRepo),
            addressRepository: $addressRepo,
        );
        
        $repo->createWithAddress(
            user: [
                'id' => 5,
                'email' => 'tom@example.com',
                'smartphone' => '555',
            ],
        );
        
        $updatedUser = $repo->updateWithAddress(
            id: 5,
            user: [
                'email' => 'tim@example.com',
                'smartphone' => '333',
            ],
            address: [
                'firstname' => 'Tim',
                'lastname' => 'Thomsen',
            ],
        );
        
        $this->assertSame(5, $updatedUser->id());
        $this->assertSame('333', $updatedUser->smartphone());
        $this->assertSame('Tim', $updatedUser->address()->firstname());
        
        $user = $repo->findById(5);
        $this->assertSame(5, $user->id());
        $this->assertSame('333', $user->smartphone());
        $this->assertSame('Tim', $user->address()->firstname());
        
        $this->assertSame(1, $repo->findAll()->count());
        $this->assertSame(1, $addressRepo->findAll()->count());        
    }
    
    public function testDeleteWithAddressesMethod()
    {
        $addressRepo = Factory::createAddressRepository();
        
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(addressRepository: $addressRepo),
            addressRepository: $addressRepo,
        );
        
        $repo->createWithAddress(
            user: [
                'id' => 5,
                'email' => 'tom@example.com',
                'smartphone' => '555',
            ],
            address: [
                'firstname' => 'Tom',
                'lastname' => 'Taylor',
            ],
        );
        
        $deletedUser = $repo->deleteWithAddresses(id: 5);
        
        $this->assertSame(5, $deletedUser->id());
        $this->assertSame('555', $deletedUser->smartphone());
        $this->assertSame('Tom', $deletedUser->address()->firstname());
        
        $this->assertSame(0, $repo->findAll()->count());
        $this->assertSame(0, $addressRepo->findAll()->count());
    }
    
    public function testAddVerifiedMethod()
    {
        $addressRepo = Factory::createAddressRepository();
        
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(addressRepository: $addressRepo),
            addressRepository: $addressRepo,
        );
        
        $repo->create(['id' => 5, 'email' => 'tom@example.com']);
        
        $updatedUser = $repo->addVerified(
            id: 5,
            channel: 'email',
            verifiedAt: new DateTime('2023-09-24 00:00:00'),
        );
        
        $this->assertSame(5, $updatedUser->id());
        $this->assertSame(['email' => '2023-09-24 00:00:00'], $updatedUser->getVerified());
        
        $user = $repo->findById(5);
        $this->assertSame(5, $user->id());
        $this->assertSame(['email' => '2023-09-24 00:00:00'], $user->getVerified());
        
        // add new channel:
        $updatedUser = $repo->addVerified(
            id: 5,
            channel: 'smartphone',
            verifiedAt: new DateTime('2024-09-24 00:00:00'),
        );
        
        $user = $repo->findById(5);
        $this->assertSame(5, $user->id());
        $this->assertSame(
            ['email' => '2023-09-24 00:00:00', 'smartphone' => '2024-09-24 00:00:00'],
            $user->getVerified()
        ); 
    }
    
    public function testRemoveVerifiedMethod()
    {
        $addressRepo = Factory::createAddressRepository();
        
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: Factory::createUserFactory(addressRepository: $addressRepo),
            addressRepository: $addressRepo,
        );
        
        $repo->create([
            'id' => 5,
            'email' => 'tom@example.com',
            'verified' => ['email' => '2023-09-24 00:00:00', 'smartphone' => '2024-09-24 00:00:00'],
        ]);
        
        $updatedUser = $repo->removeVerified(id: 5, channel: 'email');
        
        $user = $repo->findById(5);
        $this->assertSame(5, $user->id());
        $this->assertSame(['smartphone' => '2024-09-24 00:00:00'], $user->getVerified());
        
        $updatedUser = $repo->removeVerified(id: 5, channel: 'smartphone');
        
        $user = $repo->findById(5);
        $this->assertSame(5, $user->id());
        $this->assertSame([], $user->getVerified());
    }    
}