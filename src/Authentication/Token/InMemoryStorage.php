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
use Psr\Clock\ClockInterface;
use DateTimeInterface;

/**
 * InMemoryStorage
 */
final class InMemoryStorage implements TokenStorageInterface
{
    /**
     * Create a new InMemoryStorage.
     *
     * @param ClockInterface $clock
     * @param array<string, TokenInterface> $tokens
     */
    public function __construct(
        private ClockInterface $clock,
        private array $tokens = [],
    ) {}

    /**
     * Returns a new instance with the specified clock.
     *
     * @param ClockInterface $clock
     * @return static
     */
    public function withClock(ClockInterface $clock): static
    {
        return new static($clock, $this->tokens);
    }
    
    /**
     * Returns token storage name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'inmemory';
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
        if (!array_key_exists($id, $this->tokens)) {
            throw new TokenNotFoundException('Data not found');
        }
        
        $token = $this->tokens[$id];

        if (!hash_equals($token->id(), $id)) {
            throw new InvalidTokenException(message: 'Invalid token id', token: $token);
        }
        
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
        
        $token = new Token(
            id: $this->randomHash(128),
            payload: $payload,
            authenticatedVia: $authenticatedVia,
            authenticatedBy: $authenticatedBy,
            issuedBy: static::class,
            issuedAt: $issuedAt ?: $this->clock->now(),
            expiresAt: $expiresAt,
        );
        
        $this->tokens[$token->id()] = $token;
        
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
        unset($this->tokens[$token->id()]);
    }
    
    /**
     * Create a rand hash.
     *
     * @param int $length
     * @return string
     */
    private function randomHash(int $length): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}