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
use Tobento\App\User\Authenticator\TokenVerifiers;
use Tobento\App\User\Authenticator\TokenPasswordHashVerifier;
use Tobento\App\User\Authenticator\TokenVerifierInterface;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\Exception\AuthenticationException;
use DateTimeImmutable;

class TokenVerifiersTest extends TestCase
{
    public function testImplementsTokenVerifierInterface()
    {
        $this->assertInstanceOf(TokenVerifierInterface::class, new TokenVerifiers());
    }
    
    public function testVerifiersMethod()
    {
        $verifier = new TokenPasswordHashVerifier();
        $verifiers = new TokenVerifiers($verifier);
        
        $this->assertSame([$verifier], $verifiers->verifiers());
    }

    public function testVerifyPassesWithoutAnyVerifier()
    {
        $verifier = new TokenVerifiers();
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'hashedPassword'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $user = new User(
            id: 1,
            password: 'hashedPassword',
        );
        
        $verifier->verify(token: $token, user: $user);
        
        $this->assertTrue(true);
    }
    
    public function testVerifyPasses()
    {
        $verifier = new TokenVerifiers(
            new TokenPasswordHashVerifier(),
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'hashedPassword'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $user = new User(
            id: 1,
            password: 'hashedPassword',
        );
        
        $verifier->verify(token: $token, user: $user);
        
        $this->assertTrue(true);
    }
    
    public function testVerifyFails()
    {
        $this->expectException(AuthenticationException::class);
        
        $verifier = new TokenVerifiers(
            new TokenPasswordHashVerifier(),
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'hashedPasswordInvalid'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $user = new User(
            id: 1,
            password: 'hashedPassword',
        );
        
        $verifier->verify(token: $token, user: $user);
    }
}