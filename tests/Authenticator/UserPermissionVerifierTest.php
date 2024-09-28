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

namespace Tobento\App\User\Test\Authenticator;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tobento\App\User\Authenticator\UserPermissionVerifier;
use Tobento\App\User\Authenticator\UserVerifierInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\App\User\User;
use Tobento\Service\Acl\Acl;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Acl\Role;
use Tobento\Service\Container\Container;
use Tobento\Service\HelperFunction\Functions;

class UserPermissionVerifierTest extends TestCase
{
    public function testImplementsUserVerifierInterface()
    {
        $this->assertInstanceOf(UserVerifierInterface::class, new UserPermissionVerifier('permission'));
    }
    
    public function testPermissionMethod()
    {
        $this->assertSame('permission', (new UserPermissionVerifier('permission'))->permission());
    }    
    
    public function testVerifyPassesIfMatchingPermission()
    {
        $container = new Container();
        $functions = new Functions();
        $functions->register(__DIR__.'/../../vendor/tobento/service-acl/src/functions.php');
        $functions->set(ContainerInterface::class, $container);
        
        $acl = new Acl();
        $acl->rule('article.read');
        $container->set(AclInterface::class, $acl);
        
        $user = new User(id: 1);
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article.read']);
        
        (new UserPermissionVerifier('article.read'))->verify(user: $user);
        
        $this->assertTrue(true);
    }

    public function testVerifyFailsIfPermissionDoesNotMatch()
    {
        $this->expectException(AuthenticationException::class);
                
        $container = new Container();
        $functions = new Functions();
        $functions->register(__DIR__.'/../../vendor/tobento/service-acl/src/functions.php');
        $functions->set(ContainerInterface::class, $container);
        
        $acl = new Acl();
        $acl->rule('article.read');
        $container->set(AclInterface::class, $acl);
        
        $user = new User(id: 1);
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article.read']);
        
        (new UserPermissionVerifier('article.write'))->verify(user: $user);
    }
}