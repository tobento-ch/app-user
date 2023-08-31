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
use Tobento\App\User\Authenticator\UserVerifiers;
use Tobento\App\User\Authenticator\UserRoleVerifier;
use Tobento\App\User\Authenticator\UserVerifierInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\Service\Acl\Role;

class UserVerifiersTest extends TestCase
{
    public function testImplementsUserVerifierInterface()
    {
        $this->assertInstanceOf(UserVerifierInterface::class, new UserVerifiers());
    }
    
    public function testVerifiersMethod()
    {
        $verifier = new UserRoleVerifier();
        $verifiers = new UserVerifiers($verifier);
        
        $this->assertSame([$verifier], $verifiers->verifiers());
    }

    public function testVerifyPassesWithoutAnyVerifier()
    {
        $user = new User(id: 1);
        
        (new UserVerifiers())->verify(user: $user);
        
        $this->assertTrue(true);
    }
    
    public function testVerifyPasses()
    {
        $user = new User(id: 1);
        $user->setRole(new Role('editor'));
        
        $verifier = new UserVerifiers(
            new UserRoleVerifier('editor'),
        );
        
        $verifier->verify(user: $user);
        
        $this->assertTrue(true);
    }
    
    public function testVerifyFails()
    {
        $this->expectException(AuthenticationException::class);
        
        $user = new User(id: 1);
        $user->setRole(new Role('editor'));
        
        $verifier = new UserVerifiers(
            new UserRoleVerifier('author'),
        );
        
        $verifier->verify(user: $user);
    }
}