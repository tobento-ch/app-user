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

use Tobento\App\User\UserInterface;
use Tobento\App\User\Exception\AuthenticationException;

/**
 * UserVerifiers
 */
final class UserVerifiers implements UserVerifierInterface
{
    /**
     * @var array<int, UserVerifierInterface>
     */
    private array $verifiers = [];
    
    /**
     * Create a new UserVerifiers.
     *
     * @param UserVerifierInterface ...$verifier
     */
    public function __construct(
        UserVerifierInterface ...$verifier,
    ) {
        $this->verifiers = $verifier;
    }
    
    /**
     * Verify user.
     *
     * @param UserInterface $user
     * @return void
     * @throws AuthenticationException If user is verification fails.
     */
    public function verify(UserInterface $user): void
    {
        foreach($this->verifiers as $verifier) {
            $verifier->verify($user);
        }
    }
    
    /**
     * Returns the verifiers.
     *
     * @return array<int, UserVerifierInterface>
     */
    public function verifiers(): array
    {
        return $this->verifiers;
    }
}