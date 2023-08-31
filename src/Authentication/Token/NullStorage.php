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
use DateTimeInterface;

/**
 * NullStorage
 */
final class NullStorage implements TokenStorageInterface
{
    /**
     * Returns token storage name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'null';
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
        throw new TokenNotFoundException();
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
        throw new TokenCreateException('Null storage cannot create tokens');
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
        throw new TokenDeleteException('Null storage cannot delete tokens');
    }
}