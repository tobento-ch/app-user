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

namespace Tobento\App\User\Test\Console;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Console\AclRolesCommand;
use Tobento\Service\Acl\Acl;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Acl\Role;
use Tobento\Service\Console\Test\TestCommand;
use Tobento\Service\Container\Container;
    
class AclRolesCommandTest extends TestCase
{    
    public function testCommand()
    {
        $acl = new Acl();
        $acl->setRoles([
            (new Role(key: 'guest', areas: ['frontend']))->addPermissions(['article.read', 'article.write']),
            new Role(key: 'editor', areas: ['backend']),
        ]);
        
        $container = new Container();
        $container->set(AclInterface::class, $acl);
        
        $rows = [];
        
        foreach($acl->getRoles() as $role) {
            $rows[] = [
                $role->key(),
                $role->name(),
                $role->active(),
                implode(', ', $role->areas()),
                implode(', ', $role->getPermissions()),
            ];
        }
        
        (new TestCommand(command: AclRolesCommand::class))
            ->expectsTable(
                headers: ['Key', 'Name', 'Active', 'Areas', 'Permissions'],
                rows: $rows,
            )
            ->expectsExitCode(0)
            ->execute($container);
    }
}