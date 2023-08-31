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

use Tobento\App\User\UserInterface;

/**
 * Event after the user is created.
 */
final class UserCreated
{
    /**
     * Create a new UserCreated.
     *
     * @param UserInterface $user
     */
    public function __construct(
        private UserInterface $user,
    ) {}
    
    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function user(): UserInterface
    {
        return $this->user;
    }
}