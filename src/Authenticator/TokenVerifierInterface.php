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
 * TokenVerifierInterface
 */
interface TokenVerifierInterface
{
    /**
     * Verify token.
     *
     * @param TokenInterface $token
     * @param UserInterface $user
     * @return void
     * @throws AuthenticationException If token verification fails.
     */
    public function verify(TokenInterface $token, UserInterface $user): void;
}