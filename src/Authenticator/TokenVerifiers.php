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

/**
 * TokenVerifiers
 */
final class TokenVerifiers implements TokenVerifierInterface
{
    /**
     * @var array<int, TokenVerifierInterface>
     */
    private array $verifiers = [];
    
    /**
     * Create a new TokenVerifiers.
     *
     * @param TokenVerifierInterface ...$verifier
     */
    public function __construct(
        TokenVerifierInterface ...$verifier,
    ) {
        $this->verifiers = $verifier;
    }
    
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
        foreach($this->verifiers as $verifier) {
            $verifier->verify($token, $user);
        }
    }
    
    /**
     * Returns the verifiers.
     *
     * @return array<int, TokenVerifierInterface>
     */
    public function verifiers(): array
    {
        return $this->verifiers;
    }
}