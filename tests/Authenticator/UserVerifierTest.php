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
use Tobento\App\User\UserInterface;
use Tobento\App\User\Authenticator\UserVerifier;
use Tobento\App\User\Authenticator\UserVerifierInterface;
use Tobento\App\User\Exception\AuthenticationException;

class UserVerifierTest extends TestCase
{
    public function testImplementsUserVerifierInterface()
    {
        $this->assertInstanceOf(UserVerifierInterface::class, new UserVerifier(verified: true));
    }
    
    public function testVerifyPassesIfTrue()
    {
        $user = new User(id: 1);
        
        (new UserVerifier(true))->verify(user: $user);
        
        $this->assertTrue(true);
    }

    public function testVerifyFailsIfFalse()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Msg');
                
        $user = new User(id: 1);
        
        (new UserVerifier(false, 'Msg'))->verify(user: $user);
    }
    
    public function testVerifyPassesIfUsingClosure()
    {
        $user = new User(id: 1, active: true);
        
        (new UserVerifier(fn (UserInterface $user): bool => $user->active()))->verify(user: $user);
        
        $this->assertTrue(true);
    }
    
    public function testVerifyFailsUsingClosureReturningFalse()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Msg');
                
        $user = new User(id: 1, active: false);
        
        (new UserVerifier(
            verified: fn (UserInterface $user): bool => $user->active(),
            message: 'Msg',
        ))->verify(user: $user);
    }
}