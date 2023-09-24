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

namespace Tobento\App\User\Authenticator;

use Tobento\App\User\Authentication\Token\TokenInterface;
use Tobento\App\User\UserInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\App\User\Exception\TokenException;
use Tobento\App\User\Exception\InvalidTokenException;

/**
 * TokenPasswordHashVerifier
 */
final class TokenPasswordHashVerifier implements TokenVerifierInterface
{
    /**
     * Create a new TokenPasswordHashVerifiers.
     *
     * @param array $issuers The token issuers (storage names) to verify password hash.
     *                       If empty it gets verified for all issuers.
     * @param null|string $authenticatedVia If specified, it gets verified only on those.
     * @param string $name
     */
    public function __construct(
        private array $issuers = [],
        private null|string $authenticatedVia = null,
        private string $name = 'passwordHash',
    ) {}
    
    /**
     * Verify token.
     *
     * @param TokenInterface $token
     * @param UserInterface $user
     * @return void
     * @throws AuthenticationException If token verification fails.
     */
    public function verify(TokenInterface $token, UserInterface $user): void
    {
        if (
            !empty($this->issuers)
            && !in_array($token->issuedBy(), $this->issuers)
        ) {
            return;
        }
        
        if (
            !is_null($this->authenticatedVia)
            && !in_array($token->authenticatedVia(), explode('|', $this->authenticatedVia))
        ) {
            return;
        }
        
        if (!isset($token->payload()[$this->name])) {
            throw new TokenException(message: 'Missing password hash', token: $token);
        }
        
        if ($token->payload()[$this->name] !== $user->password()) {
            throw new InvalidTokenException(message: 'User password hash mismatch', token: $token);
        }
    }
}