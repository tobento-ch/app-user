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
use Tobento\App\User\Authentication\Token\SessionStorage;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\Exception\TokenNotFoundException;
use Tobento\App\User\Exception\InvalidTokenException;
use Tobento\App\User\Exception\TokenExpiredException;
use Tobento\App\User\Test\Factory;
use Tobento\Service\Clock\FrozenClock;
use DateTimeImmutable;
use DateTimeInterface;

class SessionStorageTest extends TestCase
{
    public function testThatImplementsTokenStorageInterface()
    {
        $this->assertInstanceOf(
            TokenStorageInterface::class,
            new SessionStorage(
                session: Factory::createSession(),
                clock: new FrozenClock(),
            )
        );
    }
    
    public function testNameMethod()
    {
        $storage = new SessionStorage(
            session: Factory::createSession(),
            clock: new FrozenClock(),
        );
        
        $this->assertSame('session', $storage->name());
    }
    
    public function testCreateTokenMethod()
    {
        $storage = new SessionStorage(
            session: Factory::createSession(),
            clock: new FrozenClock(),
            regenerateId: false,
        );
        
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
        $storage = new SessionStorage(
            session: Factory::createSession(),
            clock: new FrozenClock(),
            regenerateId: false,
        );
        
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
    
    public function testBadToken()
    {
        $this->expectException(InvalidTokenException::class);
        
        $storage = new SessionStorage(
            session: Factory::createSession(),
            clock: new FrozenClock(),
            regenerateId: false,
        );
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );
        
        $storage->fetchToken('bad-token');
    }

    public function testNotExpiredToken()
    {
        $session = Factory::createSession();
        
        $storage = new SessionStorage(
            session: $session,
            clock: new FrozenClock(),
            regenerateId: false,
        );
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: new DateTimeImmutable('+10 minutes'),
        );
        
        $storage = new SessionStorage(
            session: $session,
            clock: (new FrozenClock())->modify('+9 minutes'),
            regenerateId: false,
        );
        
        $fetchedToken = $storage->fetchToken($token->id());
        
        $this->assertTrue($token->id() === $fetchedToken->id());
    }
    
    public function testExpiredToken()
    {
        $this->expectException(TokenExpiredException::class);
        
        $session = Factory::createSession();
        
        $storage = new SessionStorage(
            session: $session,
            clock: new FrozenClock(),
            regenerateId: false,
        );
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: new DateTimeImmutable('+10 minutes'),
        );
        
        $storage = new SessionStorage(
            session: $session,
            clock: (new FrozenClock())->modify('+11 minutes'),
            regenerateId: false,
        );
        
        $storage->fetchToken($token->id());
    }
    
    public function testTokenNotFoundIfNone()
    {
        $this->expectException(TokenNotFoundException::class);
        
        $session = Factory::createSession();
        $session->deleteAll();
        
        $storage = new SessionStorage(
            session: $session,
            clock: new FrozenClock(),
            regenerateId: false,
        );
        
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
        
        $storage = new SessionStorage(
            session: Factory::createSession(),
            clock: new FrozenClock(),
            regenerateId: false,
        );
        
        $storage->deleteToken($token);
        
        $this->assertTrue(true);
    }
}