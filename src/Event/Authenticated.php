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

namespace Tobento\App\User\Event;

use Tobento\App\User\Authentication\AuthenticatedInterface;

/**
 * Event after authentication.
 */
final class Authenticated
{
    /**
     * Create a new Authenticated.
     *
     * @param AuthenticatedInterface $authenticated
     */
    public function __construct(
        private AuthenticatedInterface $authenticated,
    ) {}
    
    /**
     * Returns the authenticated.
     *
     * @return AuthenticatedInterface
     */
    public function authenticated(): AuthenticatedInterface
    {
        return $this->authenticated;
    }
}