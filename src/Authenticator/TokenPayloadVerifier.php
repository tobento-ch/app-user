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
 * Verifies a token payload attribute.
 */
final class TokenPayloadVerifier implements TokenVerifierInterface
{
    /**
     * Create a new TokenPayloadVerifier.
     *
     * @param string $name The payload attribute name.
     * @param mixed $value The value to compare with the payload value.
     * @param array $issuers The token issuers (storage names) to verify password hash.
     *                       If empty it gets verified for all issuers.
     * @param null|string $authenticatedVia If specified, it gets verified only on those.
     */
    public function __construct(
        private string $name,
        private mixed $value,
        private array $issuers = [],
        private null|string $authenticatedVia = null,
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
            throw new TokenException(
                message: sprintf('Missing token payload: %s', $this->name),
                token: $token
            );
        }
        
        if ($token->payload()[$this->name] !== $this->value) {
            throw new InvalidTokenException(
                message: sprintf('Token payload %s mismatch', $this->name),
                token: $token
            );
        }
    }
}