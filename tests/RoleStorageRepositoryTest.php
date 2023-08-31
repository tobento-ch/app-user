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
use Tobento\App\User\RoleStorageRepository;
use Tobento\App\User\RoleRepositoryInterface;
use Tobento\Service\Storage\InMemoryStorage;
    
class RoleStorageRepositoryTest extends TestCase
{    
    public function testThatImplementsRoleRepositoryInterface()
    {
        $this->assertInstanceOf(
            RoleRepositoryInterface::class,
            new RoleStorageRepository(
                storage: new InMemoryStorage([]),
                table: 'roles',
                entityFactory: Factory::createRoleFactory(),
            )
        );
    }
    
    public function testFindOneMethod()
    {
        $repo = new RoleStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'roles',
            entityFactory: Factory::createRoleFactory(),
        );
        
        $this->assertNull($repo->findOne());
        
        $repo->create(['key' => 'editor']);
        $repo->create(['key' => 'guest']);
        
        $this->assertSame('editor', $repo->findOne()?->key());
        $this->assertSame('guest', $repo->findOne(where: ['key' => 'guest'])?->key());
    }
    
    public function testFindByKeyMethod()
    {
        $repo = new RoleStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'roles',
            entityFactory: Factory::createRoleFactory(),
        );
        
        $this->assertNull($repo->findByKey('editor'));
        
        $repo->create(['key' => 'editor']);
        
        $this->assertSame('editor', $repo->findByKey('editor')?->key());
    }
}