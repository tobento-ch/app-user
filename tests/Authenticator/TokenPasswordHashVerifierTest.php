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
use Tobento\App\User\Authenticator\TokenPasswordHashVerifier;
use Tobento\App\User\Authenticator\TokenVerifierInterface;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\Exception\TokenException;
use Tobento\App\User\Exception\InvalidTokenException;
use DateTimeImmutable;

class TokenPasswordHashVerifierTest extends TestCase
{
    public function testImplementsTokenVerifierInterface()
    {
        $this->assertInstanceOf(TokenVerifierInterface::class, new TokenPasswordHashVerifier());
    }
    
    public function testVerify()
    {
        $verifier = new TokenPasswordHashVerifier();
        
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
    
    public function testVerifyFailsIfPasswordHashDoesNotMatch()
    {
        $this->expectException(InvalidTokenException::class);
        
        $verifier = new TokenPasswordHashVerifier();
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'password'],
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
    
    public function testVerifyFailsIfInvalidPayload()
    {
        $this->expectException(TokenException::class);
        
        $verifier = new TokenPasswordHashVerifier();
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2],
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
    
    public function testVerifyWithIssuers()
    {
        $this->expectException(TokenException::class);
        
        $verifier = new TokenPasswordHashVerifier(
            issuers: ['session']
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'hashedPasswordFoo'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'session',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $user = new User(
            id: 1,
            password: 'hashedPassword',
        );
        
        $verifier->verify(token: $token, user: $user);
    }
    
    public function testVerifyWithIssuersPassesIfNotMatchingIssuer()
    {
        $verifier = new TokenPasswordHashVerifier(
            issuers: ['session']
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'hashedPasswordFoo'],
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
    
    public function testVerifyWithAuthenticatedVia()
    {
        $this->expectException(TokenException::class);
        
        $verifier = new TokenPasswordHashVerifier(
            authenticatedVia: 'remembered|loginlink',
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'hashedPasswordFoo'],
            authenticatedVia: 'remembered',
            authenticatedBy: null,
            issuedBy: 'session',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $user = new User(
            id: 1,
            password: 'hashedPassword',
        );
        
        $verifier->verify(token: $token, user: $user);
    }
    
    public function testVerifyWithAuthenticatedViaPassesIfNotMatchingVia()
    {
        $verifier = new TokenPasswordHashVerifier(
            authenticatedVia: 'remembered|loginlink',
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'hashedPasswordFoo'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'session',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $user = new User(
            id: 1,
            password: 'hashedPassword',
        );
        
        $verifier->verify(token: $token, user: $user);
        
        $this->assertTrue(true);
    }
    
    public function testVerifyWithSpecificName()
    {
        $verifier = new TokenPasswordHashVerifier(
            name: 'pwHash',
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'pwHash' => 'hashedPassword'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'session',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $user = new User(
            id: 1,
            password: 'hashedPassword',
        );
        
        $verifier->verify(token: $token, user: $user);
        
        $this->assertTrue(true);
    }
    
    public function testVerifyWithSpecificNameFailsIfInvalidPayload()
    {
        $this->expectException(TokenException::class);
        
        $verifier = new TokenPasswordHashVerifier(
            name: 'pwHash',
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 2, 'passwordHash' => 'hashedPassword'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'session',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $user = new User(
            id: 1,
            password: 'hashedPassword',
        );
        
        $verifier->verify(token: $token, user: $user);
    }
}