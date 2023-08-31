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
use Tobento\App\User\RoleFactory;
use Tobento\App\User\RoleFactoryInterface;
    
class RoleFactoryTest extends TestCase
{    
    public function testThatImplementsRoleFactoryInterface()
    {
        $this->assertInstanceOf(
            RoleFactoryInterface::class,
            new RoleFactory()
        );
    }
    
    public function testCreateEntityFromArrayMethod()
    {
        $roleFactory = new RoleFactory();
        
        $role = $roleFactory->createEntityFromArray([
            'key' => 'editor',
            'areas' => ['backend'],
            'active' => true,
            'name' => 'EDITOR',
        ]);
        
        $this->assertSame('editor', $role->key());
        $this->assertSame(['backend'], $role->areas());
        $this->assertTrue($role->active());
        $this->assertSame('EDITOR', $role->name());
    }
    
    public function testCreateEntityFromArrayMethodWithPermissions()
    {
        $roleFactory = new RoleFactory();
        
        $role = $roleFactory->createEntityFromArray([
            'key' => 'editor',
            'permissions' => ['article.create', 'article.update'],
        ]);
        
        $this->assertSame(['article.create', 'article.update'], $role->getPermissions());
    }
}