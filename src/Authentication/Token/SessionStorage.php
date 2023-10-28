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
use Tobento\App\User\Exception\InvalidTokenException;
use Tobento\App\User\Exception\TokenExpiredException;
use Tobento\Service\Session\SessionInterface;
use Psr\Clock\ClockInterface;
use DateTimeInterface;
use Throwable;

/**
 * SessionStorage
 */
final class SessionStorage implements TokenStorageInterface
{
    private const SESSION_KEY = '_authToken';
    
    /**
     * Create a new SessionStorage.
     *
     * @param SessionInterface $session
     * @param ClockInterface $clock
     * @param bool $regenerateId
     */
    public function __construct(
        private SessionInterface $session,
        private ClockInterface $clock,
        private bool $regenerateId = true,
    ) {}
    
    /**
     * Returns token storage name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'session';
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
        if (! $this->session->has(key: static::SESSION_KEY)) {
            // throws if session has expired as session is empty.
            throw new TokenNotFoundException('Data not found');
        }
        
        $data = $this->session->get(key: static::SESSION_KEY);

        try {
            $token = Token::fromJsonString($data);
        } catch (Throwable $e) {
            throw new InvalidTokenException('Unable to create session token', (int)$e->getCode(), $e);
        }

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
        
        $this->session->set(key: static::SESSION_KEY, value: json_encode($token));
        
        if ($this->regenerateId) {
            $this->session->regenerateId();
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
        $this->session->delete(key: static::SESSION_KEY);
        
        if ($this->regenerateId) {
            $this->session->regenerateId();
        }
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
}