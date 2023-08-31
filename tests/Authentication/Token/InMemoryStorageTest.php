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

namespace Tobento\App\Test\Authentication\Token;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Authentication\Token\InMemoryStorage;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\Exception\TokenNotFoundException;
use Tobento\App\User\Exception\InvalidTokenException;
use Tobento\App\User\Exception\TokenExpiredException;
use Tobento\App\User\Test\Factory;
use Tobento\Service\Clock\FrozenClock;
use DateTimeImmutable;
use DateTimeInterface;

class InMemoryStorageTest extends TestCase
{
    public function testThatImplementsTokenStorageInterface()
    {
        $this->assertInstanceOf(
            TokenStorageInterface::class,
            new InMemoryStorage(clock: new FrozenClock())
        );
    }
    
    public function testNameMethod()
    {
        $storage = new InMemoryStorage(clock: new FrozenClock());
        
        $this->assertSame('inmemory', $storage->name());
    }
    
    public function testCreateTokenMethod()
    {
        $storage = new InMemoryStorage(clock: new FrozenClock());
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );
        
        $this->assertSame(128, strlen($token->id()));
        $this->assertSame(['userId' => 1], $token->payload());
        $this->assertSame('via', $token->authenticatedVia());
        $this->assertSame('by', $token->authenticatedBy());
        $this->assertSame($storage::class, $token->issuedBy());
        $this->assertInstanceof(DateTimeInterface::class, $token->issuedAt());
        $this->assertSame(null, $token->expiresAt());
    }
    
    public function testValidToken()
    {
        $storage = new InMemoryStorage(clock: new FrozenClock());
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );
        
        $fetchedToken = $storage->fetchToken($token->id());
        
        $this->assertTrue($token->id() === $fetchedToken->id());
    }

    public function testNotExpiredToken()
    {
        $storage = new InMemoryStorage(clock: new FrozenClock());
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: new DateTimeImmutable('+10 minutes'),
        );
        
        $storage = $storage->withClock(
            clock: (new FrozenClock())->modify('+9 minutes'),
        );
        
        $fetchedToken = $storage->fetchToken($token->id());
        
        $this->assertTrue($token->id() === $fetchedToken->id());
    }
    
    public function testExpiredToken()
    {
        $this->expectException(TokenExpiredException::class);
        
        $storage = new InMemoryStorage(clock: new FrozenClock());
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: new DateTimeImmutable('+10 minutes'),
        );
        
        $storage = $storage->withClock(
            clock: (new FrozenClock())->modify('+11 minutes'),
        );
        
        $storage->fetchToken($token->id());
    }
    
    public function testTokenNotFoundIfNone()
    {
        $this->expectException(TokenNotFoundException::class);
        
        $storage = new InMemoryStorage(clock: new FrozenClock());
        
        $storage->fetchToken('ID');
    }

    public function testDeleteTokenMethod()
    {
        $token = new Token(
            id: 'ID',
            payload: [],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedBy: 'storageName',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $storage = new InMemoryStorage(clock: new FrozenClock());
        
        $storage->deleteToken($token);
        
        $this->assertTrue(true);
    }
}