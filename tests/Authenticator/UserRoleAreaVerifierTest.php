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
use Tobento\App\User\Authenticator\UserRoleAreaVerifier;
use Tobento\App\User\Authenticator\UserVerifierInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\Service\Acl\Role;

class UserRoleAreaVerifierTest extends TestCase
{
    public function testImplementsUserVerifierInterface()
    {
        $this->assertInstanceOf(UserVerifierInterface::class, new UserRoleAreaVerifier());
    }
    
    public function testAreasMethod()
    {
        $this->assertSame([], (new UserRoleAreaVerifier())->areas());
        $this->assertSame(['frontend', 'api'], (new UserRoleAreaVerifier('frontend', 'api'))->areas());
    }    
    
    public function testVerifyPassesIfMatchingArea()
    {
        $user = new User(id: 1);
        $user->setRole(new Role('guest', ['frontend', 'api']));
        
        (new UserRoleAreaVerifier('frontend'))->verify(user: $user);
        
        $this->assertTrue(true);
    }

    public function testVerifyFailsIfAreaDoesNotMatch()
    {
        $this->expectException(AuthenticationException::class);
                
        $user = new User(id: 1);
        $user->setRole(new Role('guest', ['frontend']));
        
        (new UserRoleAreaVerifier('backend'))->verify(user: $user);
    }
    
    public function testVerifyFailsIfNoAreaSpecifiedAtAll()
    {
        $this->expectException(AuthenticationException::class);
                
        $user = new User(id: 1);
        $user->setRole(new Role('guest', ['frontend']));
        
        (new UserRoleAreaVerifier())->verify(user: $user);
    }
}