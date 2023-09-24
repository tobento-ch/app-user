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
use Tobento\App\User\Authenticator\TokenPayloadVerifier;
use Tobento\App\User\Authenticator\TokenVerifierInterface;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\Exception\TokenException;
use Tobento\App\User\Exception\InvalidTokenException;
use DateTimeImmutable;

class TokenPayloadVerifierTest extends TestCase
{
    public function testImplementsTokenVerifierInterface()
    {
        $this->assertInstanceOf(
            TokenVerifierInterface::class,
            new TokenPayloadVerifier(name: 'name', value: 'value')
        );
    }
    
    public function testVerify()
    {
        $verifier = new TokenPayloadVerifier(
            name: 'name',
            value: 'value',
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['name' => 'value'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $verifier->verify(token: $token, user: new User());
        
        $this->assertTrue(true);
    }
    
    public function testVerifyFailsIfValueDoesNotMatch()
    {
        $this->expectException(InvalidTokenException::class);
        
        $verifier = new TokenPayloadVerifier(
            name: 'name',
            value: 'invalid',
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['name' => 'value'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $verifier->verify(token: $token, user: new User());
    }
    
    public function testVerifyFailsIfPayloadAttributeDoesNotExist()
    {
        $this->expectException(TokenException::class);
        
        $verifier = new TokenPayloadVerifier(
            name: 'name',
            value: 'value',
        );
        
        $token = new Token(
            id: 'ID',
            payload: [],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $verifier->verify(token: $token, user: new User());
    }
    
    public function testVerifyWithIssuers()
    {
        $this->expectException(TokenException::class);
        
        $verifier = new TokenPayloadVerifier(
            name: 'name',
            value: 'invalid',
            issuers: ['session'],
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['name' => 'value'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'session',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $verifier->verify(token: $token, user: new User());
    }
    
    public function testVerifyWithIssuersPassesIfNotMatchingIssuer()
    {
        $verifier = new TokenPayloadVerifier(
            name: 'name',
            value: 'value',
            issuers: ['session'],
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['name' => 'value'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $verifier->verify(token: $token, user: new User());
        
        $this->assertTrue(true);
    }
    
    public function testVerifyWithAuthenticatedVia()
    {
        $this->expectException(TokenException::class);
        
        $verifier = new TokenPayloadVerifier(
            name: 'name',
            value: 'invalid',
            authenticatedVia: 'remembered|loginlink',
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['name' => 'value'],
            authenticatedVia: 'remembered',
            authenticatedBy: null,
            issuedBy: 'session',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $verifier->verify(token: $token, user: new User());
    }
    
    public function testVerifyWithAuthenticatedViaPassesIfNotMatchingVia()
    {
        $verifier = new TokenPayloadVerifier(
            name: 'name',
            value: 'value',
            authenticatedVia: 'remembered|loginlink',
        );
        
        $token = new Token(
            id: 'ID',
            payload: ['name' => 'value'],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'session',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $verifier->verify(token: $token, user: new User());
        
        $this->assertTrue(true);
    }
}