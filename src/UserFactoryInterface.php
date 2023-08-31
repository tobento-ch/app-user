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

use Tobento\Service\Repository\Storage\StorageEntityFactoryInterface;

/**
 * UserFactoryInterface
 */
interface UserFactoryInterface extends StorageEntityFactoryInterface
{
    /**
     * Create guest user.
     *
     * @return UserInterface
     */
    public function createGuestUser(): UserInterface;
}