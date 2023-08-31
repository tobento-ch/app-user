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

namespace Tobento\App\User;

use Tobento\Service\User\UserInterface as BaseUserInterface;
use Tobento\Service\Acl\Authorizable;

/**
 * UserInterface
 */
interface UserInterface extends BaseUserInterface, Authorizable
{
    /**
     * Set if the user is authenticated.
     *
     * @param bool $isAuthenticated
     * @return static $this
     */
    public function setAuthenticated(bool $isAuthenticated): static;
    
    /**
     * Returns whether the user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool;
}