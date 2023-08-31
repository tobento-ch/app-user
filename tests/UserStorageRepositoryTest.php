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
use Tobento\App\User\UserRepositoryInterface;
use Tobento\Service\Storage\InMemoryStorage;
    
class UserStorageRepositoryTest extends TestCase
{    
    public function testThatImplementsUserRepositoryInterface()
    {
        $this->assertInstanceOf(
            UserRepositoryInterface::class,
            new UserStorageRepository(
                storage: new InMemoryStorage([]),
                table: 'users',
                entityFactory: Factory::createUserFactory(),
            )
        );
    }
    
    public function testFindOneMethod()
    {
        $repo = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            entityFactory: Factory::createUserFactory(),
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
            entityFactory: Factory::createUserFactory(),
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
            entityFactory: Factory::createUserFactory(),
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
}