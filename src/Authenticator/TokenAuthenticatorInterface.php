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
use Tobento\App\User\Authentication\Token\TokenInterface;
use Tobento\App\User\Exception\AuthenticationException;

/**
 * TokenAuthenticatorInterface
 */
interface TokenAuthenticatorInterface
{
    /**
     * Authenticate token.
     *
     * @param TokenInterface $token
     * @return UserInterface
     * @throws AuthenticationException If authentication fails.
     */
    public function authenticate(TokenInterface $token): UserInterface;
}