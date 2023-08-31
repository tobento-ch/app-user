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
use Tobento\App\User\Test\Factory;
use Tobento\App\User\Authenticator\TokenAuthenticator;
use Tobento\App\User\Authenticator\TokenAuthenticatorInterface;
use Tobento\App\User\Authenticator\TokenPasswordHashVerifier;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\Exception\AuthenticationException;
use DateTimeImmutable;

class TokenAuthenticatorTest extends TestCase
{
    public function testConstructMethod()
    {
        $authenticator = new TokenAuthenticator(
            userRepository: Factory::createUserRepository(),
        );

        $this->assertInstanceOf(TokenAuthenticatorInterface::class, $authenticator);
    }
    
    public function testAuthenticate()
    {
        $authenticator = new TokenAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['id' => 2, 'email' => 'tom@example.com'],
            ]),
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );

        $user = $authenticator->authenticate($token);
        
        $this->assertTrue(true);
    }

    public function testAuthenticateFailsWithInvalidPayload()
    {
        $this->expectException(AuthenticationException::class);
        
        $authenticator = new TokenAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['id' => 2, 'email' => 'tom@example.com'],
            ]),
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['user_id' => 2],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );

        $user = $authenticator->authenticate($token);
    }
    
    public function testAuthenticateFailsIfUserNotFound()
    {
        $this->expectException(AuthenticationException::class);
        
        $authenticator = new TokenAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['id' => 2, 'email' => 'tom@example.com'],
            ]),
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 5],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );

        $user = $authenticator->authenticate($token);
    }
    
    public function testAuthenticateWithTokenVerifiers()
    {
        $passwordHasher = Factory::createPasswordHasher();
        $hashedPassword = $passwordHasher->hash('123456');
        
        $authenticator = new TokenAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['id' => 2, 'email' => 'tom@example.com', 'password' => $hashedPassword],
            ]),
            tokenVerifier: new TokenPasswordHashVerifier(),
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => $hashedPassword],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );

        $user = $authenticator->authenticate($token);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateWithTokenVerifiersFails()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();
        $hashedPassword = $passwordHasher->hash('123456');
        
        $authenticator = new TokenAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['id' => 2, 'email' => 'tom@example.com', 'password' => $hashedPassword],
            ]),
            tokenVerifier: new TokenPasswordHashVerifier(),
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'invalidhash'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );

        $user = $authenticator->authenticate($token);
    }
}