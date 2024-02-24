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

namespace Tobento\App\User\Authentication\Token;

use Tobento\App\User\Exception\TokenException;
use Tobento\App\User\Exception\TokenCreateException;
use Tobento\App\User\Exception\TokenDeleteException;
use Tobento\App\User\Exception\TokenNotFoundException;
use Tobento\App\User\Exception\TokenExpiredException;
use Tobento\App\User\Exception\InvalidTokenException;
use Tobento\Service\Repository\RepositoryCreateException;
use Tobento\Service\Repository\RepositoryDeleteException;
use Psr\Clock\ClockInterface;
use DateTimeInterface;
use Throwable;

/**
 * RepositoryStorage
 */
final class RepositoryStorage implements TokenStorageInterface, CanDeleteExpiredTokens
{
    /**
     * Create a new RepositoryStorage.
     *
     * @param ClockInterface $clock
     * @param TokenRepository $repository
     * @param string $name
     */
    public function __construct(
        private ClockInterface $clock,
        private TokenRepository $repository,
        private string $name = 'repository',
    ) {}
    
    /**
     * Returns repository.
     *
     * @return TokenRepository
     */
    public function repository(): TokenRepository
    {
        return $this->repository;
    }

    /**
     * Returns a new instance with the specified clock.
     *
     * @param ClockInterface $clock
     * @return static
     */
    public function withClock(ClockInterface $clock): static
    {
        return new static($clock, $this->repository);
    }
    
    /**
     * Returns token storage name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * Returns the token.
     *
     * @param string $id
     * @return TokenInterface
     * @throws TokenException If token is not found, is expired or for any reason invalid.
     */
    public function fetchToken(string $id): TokenInterface
    {
        if (!str_contains($id, ':')) {
            throw new TokenNotFoundException('Invalid id');
        }

        [$itemId, $hash] = explode(':', $id, 2);
        
        $item = $this->repository->findById($itemId);
        
        if (is_null($item)) {
            throw new TokenNotFoundException('Data not found');
        }
        
        try {
            $token = Token::fromArray($item->get('token'));
        } catch (Throwable $e) {
            throw new InvalidTokenException('Unable to create token', (int)$e->getCode(), $e);
        }
            
        if (!hash_equals($token->id(), hash('sha512', $hash))) {
            throw new InvalidTokenException(message: 'Invalid token id', token: $token);
        }
        
        $token = $token->withId($id);
        
        if (
            !is_null($token->expiresAt())
            && $token->expiresAt() < $this->clock->now()
        ) {
            $this->deleteToken($token);
            throw new TokenExpiredException(token: $token);
        }
        
        return $token;
    }
    
    /**
     * Create a new token.
     *
     * @param array $payload
     * @param string $authenticatedVia The name of which the user was authenticated via loginlink e.g.
     * @param null|string $authenticatedBy The name of which the user was authenticated by (authenticator class name).
     * @param null|DateTimeInterface $issuedAt
     * @param null|DateTimeInterface $expiresAt
     * @return TokenInterface
     * @throws TokenCreateException
     */
    public function createToken(
        array $payload,
        string $authenticatedVia,
        null|string $authenticatedBy = null,
        null|DateTimeInterface $issuedAt = null,
        null|DateTimeInterface $expiresAt = null,
    ): TokenInterface {
        
        $itemId = $this->createUniqueItemId(length: 64);
        $tokenId = $this->randomHash(128);
        
        $token = new Token(
            id: sprintf('%s:%s', $itemId, $tokenId),
            payload: $payload,
            authenticatedVia: $authenticatedVia,
            authenticatedBy: $authenticatedBy,
            issuedBy: static::class,
            issuedAt: $issuedAt ?: $this->clock->now(),
            expiresAt: $expiresAt,
        );
        
        $storageToken = $token->withId(hash('sha512', $tokenId));
        
        try {
            $this->repository->create([
                'id' => $itemId,
                'token' => json_encode($storageToken),
                'expires_at' => $token->expiresAt(),
            ]);
        } catch (RepositoryCreateException $e) {
            throw new TokenCreateException($e->getMessage(), (int)$e->getCode(), $e, $token);
        }
        
        return $token;
    }
    
    /**
     * Delete the specified token.
     *
     * @param TokenInterface $token
     * @return void
     * @throws TokenDeleteException
     */
    public function deleteToken(TokenInterface $token): void
    {
        if (!str_contains($token->id(), ':')) {
            return;
        }

        [$itemId] = explode(':', $token->id(), 2);

        try {
            $this->repository->delete(where: ['id' => $itemId]);
        } catch (RepositoryDeleteException $e) {
            throw new TokenDeleteException($e->getMessage(), (int)$e->getCode(), $e, $token);
        }
    }
    
    /**
     * Deletes all expired tokens.
     *
     * @return bool
     *   True if the tokens were successfully deleted. False if there was an error.
     */
    public function deleteExpiredTokens(): bool
    {
        try {
            $this->repository->delete(where: [
                'expires_at' => [
                    'not null',
                    '<' => $this->clock->now()->getTimestamp()
                ],
            ]);
        } catch (RepositoryDeleteException $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Create a rand hash.
     *
     * @param positive-int $length
     * @return string
     */
    private function randomHash(int $length): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
    
    /**
     * Create a unique item id.
     *
     * @param positive-int $length
     * @return string
     */
    private function createUniqueItemId(int $length): string
    {
        $id = $this->randomHash($length);
        
        $item = $this->repository->findById($id);
        
        if (is_null($item)) {
            return $id;
        }
        
        return $this->createUniqueItemId($length);
    }
}