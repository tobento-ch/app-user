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
use Tobento\App\User\User;
use Tobento\App\User\Authenticator\UserRoleVerifier;
use Tobento\App\User\Authenticator\UserVerifierInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\Service\Acl\Role;

class UserRoleVerifierTest extends TestCase
{
    public function testImplementsUserVerifierInterface()
    {
        $this->assertInstanceOf(UserVerifierInterface::class, new UserRoleVerifier());
    }
    
    public function testRolesMethod()
    {
        $this->assertSame([], (new UserRoleVerifier())->roles());
        $this->assertSame(['editor', 'author'], (new UserRoleVerifier('editor', 'author'))->roles());
    }    
    
    public function testVerifyPassesIfMatchingRole()
    {
        $user = new User(id: 1);
        $user->setRole(new Role('editor'));
        
        (new UserRoleVerifier('editor'))->verify(user: $user);
        
        $this->assertTrue(true);
    }
    
    public function testVerifyPassesIfMatchingRoleWithMultiple()
    {
        $user = new User(id: 1);
        $user->setRole(new Role('author'));
        
        (new UserRoleVerifier('editor', 'author'))->verify(user: $user);
        
        $this->assertTrue(true);
    }

    public function testVerifyFailsIfRoleDoesNotMatch()
    {
        $this->expectException(AuthenticationException::class);
                
        $user = new User(id: 1);
        $user->setRole(new Role('editor'));
        
        (new UserRoleVerifier('author'))->verify(user: $user);
    }
    
    public function testVerifyFailsIfNoRoleSpecifiedAtAll()
    {
        $this->expectException(AuthenticationException::class);
                
        $user = new User(id: 1);
        $user->setRole(new Role('editor'));
        
        (new UserRoleVerifier())->verify(user: $user);
    }
}