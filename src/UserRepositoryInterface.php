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
use Tobento\Service\Repository\RepositoryCreateException;
use Tobento\Service\Repository\RepositoryUpdateException;
use Tobento\Service\Repository\RepositoryDeleteException;

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
    
    /**
     * Create a new user with primary address.
     *
     * @param array $user
     * @param array $address
     * @return UserInterface
     * @throws RepositoryCreateException
     */
    public function createWithAddress(array $user, array $address = []): UserInterface;
    
    /**
     * Update a user and its primary address.
     *
     * @param string|int $id The user id.
     * @param array $user
     * @param array $address
     * @return UserInterface
     * @throws RepositoryUpdateException
     * @throws RepositoryCreateException
     */
    public function updateWithAddress(string|int $id, array $user, array $address = []): UserInterface;
    
    /**
     * Delete an user with its addresses.
     *
     * @param string|int $id The user id.
     * @return UserInterface
     * @throws RepositoryDeleteException
     */
    public function deleteWithAddresses(string|int $id): UserInterface;
}