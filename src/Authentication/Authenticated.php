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

namespace Tobento\App\User\Authentication;

use Tobento\App\User\Authentication\Token\TokenInterface;
use Tobento\App\User\UserInterface;

/**
 * Authenticated
 */
class Authenticated implements AuthenticatedInterface
{
    /**
     * Create a new Authenticated.
     *
     * @param TokenInterface $token
     * @param UserInterface $user
     */
    public function __construct(
        protected TokenInterface $token,
        protected UserInterface $user,
    ) {}
    
    /**
     * Returns the token.
     *
     * @return TokenInterface
     */
    public function token(): TokenInterface
    {
        return $this->token;
    }
    
    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function user(): UserInterface
    {
        return $this->user;
    }
    
    /**
     * Returns the name of which the user was authenticated via loginlink e.g.
     *
     * @return string
     */
    public function via(): string
    {
        return $this->token()->authenticatedVia();
    }
    
    /**
     * Returns the name of which the user was authenticated by (authenticator class name).
     *
     * @return null|string
     */
    public function by(): null|string
    {
        return $this->token()->authenticatedBy();
    }
}