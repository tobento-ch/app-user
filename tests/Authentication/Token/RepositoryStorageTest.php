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
use Tobento\App\User\Authentication\Token\RepositoryStorage;
use Tobento\App\User\Authentication\Token\TokenRepository;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\Token\CanDeleteExpiredTokens;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\Exception\TokenNotFoundException;
use Tobento\App\User\Exception\InvalidTokenException;
use Tobento\App\User\Exception\TokenExpiredException;
use Tobento\App\User\Test\Factory;
use Tobento\Service\Clock\FrozenClock;
use DateTimeImmutable;
use DateTimeInterface;

use Tobento\App\User\Authentication\Token\ServiceStorage;

class RepositoryStorageTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: new InMemoryStorage([]),
                table: 'auth_tokens',
            ),
        );
        
        $this->assertInstanceOf(TokenStorageInterface::class, $storage);
        $this->assertInstanceOf(CanDeleteExpiredTokens::class, $storage);
    }
    
    public function testNameMethod()
    {
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: new InMemoryStorage([]),
                table: 'auth_tokens',
            ),
            name: 'repo',
        );
        
        $this->assertSame('repo', $storage->name());
    }
    
    public function testCreateTokenMethod()
    {
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: new InMemoryStorage([]),
                table: 'auth_tokens',
            ),
        );
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );
        
        [$itemId, $tokenId] = explode(':', $token->id(), 2);
        $this->assertSame(64, strlen($itemId));
        $this->assertSame(128, strlen($tokenId));
        
        $this->assertSame(['userId' => 1], $token->payload());
        $this->assertSame('via', $token->authenticatedVia());
        $this->assertSame('by', $token->authenticatedBy());
        $this->assertSame($storage::class, $token->issuedBy());
        $this->assertInstanceof(DateTimeInterface::class, $token->issuedAt());
        $this->assertSame(null, $token->expiresAt());
        
        $this->assertSame(1, $storage->repository()->count());
    }
    
    public function testTokenIdIsHashedInStorageForSecurity()
    {
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: new InMemoryStorage([]),
                table: 'auth_tokens',
            ),
        );
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );
        
        [$itemId, $tokenId] = explode(':', $token->id(), 2);
        
        $item = $storage->repository()->findById($itemId);
        
        $this->assertFalse(str_contains(json_encode($item->get('token')), $tokenId));
    }
    
    public function testValidToken()
    {
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: new InMemoryStorage([]),
                table: 'auth_tokens',
            ),
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
        
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: new InMemoryStorage([]),
                table: 'auth_tokens',
            ),
        );
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );
        
        $storage->fetchToken($token->id().'bad_token');
    }

    public function testNotExpiredToken()
    {
        $inMemoryStorage = new InMemoryStorage([]);
        
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: $inMemoryStorage,
                table: 'auth_tokens',
            ),
        );
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: new DateTimeImmutable('+10 minutes'),
        );
        
        $storage = new RepositoryStorage(
            clock: (new FrozenClock())->modify('+9 minutes'),
            repository: new TokenRepository(
                storage: $inMemoryStorage,
                table: 'auth_tokens',
            ),
        );
        
        $fetchedToken = $storage->fetchToken($token->id());
        
        $this->assertTrue($token->id() === $fetchedToken->id());
    }
    
    public function testExpiredToken()
    {
        $this->expectException(TokenExpiredException::class);
        
        $inMemoryStorage = new InMemoryStorage([]);
        
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: $inMemoryStorage,
                table: 'auth_tokens',
            ),
        );
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: new DateTimeImmutable('+10 minutes'),
        );
        
        $storage = new RepositoryStorage(
            clock: (new FrozenClock())->modify('+11 minutes'),
            repository: new TokenRepository(
                storage: $inMemoryStorage,
                table: 'auth_tokens',
            ),
        );
        
        $storage->fetchToken($token->id());
    }
    
    public function testTokenNotFoundIfNone()
    {
        $this->expectException(TokenNotFoundException::class);
        
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: new InMemoryStorage([]),
                table: 'auth_tokens',
            ),
        );
        
        $storage->fetchToken('ID');
    }

    public function testDeleteTokenMethod()
    {
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: new InMemoryStorage([]),
                table: 'auth_tokens',
            ),
        );
        
        $token = $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );
        
        $this->assertSame(1, $storage->repository()->count());
        
        $storage->deleteToken($token);
        
        $this->assertSame(0, $storage->repository()->count());
    }
    
    public function testDeleteExpiredTokensMethod()
    {
        $inMemoryStorage = new InMemoryStorage([]);
        
        $storage = new RepositoryStorage(
            clock: new FrozenClock(),
            repository: new TokenRepository(
                storage: $inMemoryStorage,
                table: 'auth_tokens',
            ),
        );
        
        $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: new DateTimeImmutable('+10 minutes'),
        );
        
        $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: new DateTimeImmutable('+20 minutes'),
        );
        
        $storage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );

        $storage = new RepositoryStorage(
            clock: (new FrozenClock())->modify('+15 minutes'),
            repository: new TokenRepository(
                storage: $inMemoryStorage,
                table: 'auth_tokens',
            ),
        );
        
        $this->assertSame(3, $storage->repository()->count());
        $this->assertTrue($storage->deleteExpiredTokens());
        $this->assertSame(2, $storage->repository()->count());
    }
}