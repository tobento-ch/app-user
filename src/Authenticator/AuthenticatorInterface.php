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

use Psr\Http\Message\ServerRequestInterface;
use Tobento\App\User\UserInterface;
use Tobento\App\User\Exception\AuthenticationException;

/**
 * AuthenticatorInterface
 */
interface AuthenticatorInterface
{
    /**
     * Authenticate user.
     *
     * @param ServerRequestInterface $request
     * @return UserInterface
     * @throws AuthenticationException If authentication fails.
     */
    public function authenticate(ServerRequestInterface $request): UserInterface;
}