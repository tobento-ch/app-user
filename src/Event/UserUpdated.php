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
 * Event after the user is updated.
 */
final class UserUpdated
{
    /**
     * Create a new UserUpdated.
     *
     * @param UserInterface $user
     * @param null|UserInterface $oldUser
     */
    public function __construct(
        private UserInterface $user,
        private null|UserInterface $oldUser,
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
    
    /**
     * Returns the old user.
     *
     * @return null|UserInterface
     */
    public function oldUser(): null|UserInterface
    {
        return $this->oldUser;
    }
}