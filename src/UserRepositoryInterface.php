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

use Tobento\Service\Repository\RepositoryInterface;

/**
 * UserRepositoryInterface
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Returns the found user using the specified unique identity parameters
     * usually used for login or null if none found.
     *
     * @param string $email
     * @param string $username
     * @param string $smartphone
     * @return null|UserInterface
     */
    public function findByIdentity(string $email = '', string $username = '', string $smartphone = ''): null|UserInterface;
}