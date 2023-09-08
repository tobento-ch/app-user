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
use Tobento\Service\User\AddressesInterface;
use Tobento\Service\User\AddressInterface;
use Tobento\Service\Storage\ItemsInterface;

/**
 * AddressRepositoryInterface
 */
interface AddressRepositoryInterface extends RepositoryInterface
{
    /**
     * Returns the found primary address for the user id or null if none found.
     *
     * @param int|string $userId
     * @return null|AddressInterface
     */
    public function findPrimaryByUserId(int|string $userId): null|AddressInterface;
    
    /**
     * Returns the found addresses for the user id.
     *
     * @param int|string $userId
     * @return AddressesInterface
     */
    public function findAllByUserId(int|string $userId): AddressesInterface;

    /**
     * Returns all addresses for the user ids grouped by user id.
     *
     * @param array $userIds
     * @return ItemsInterface
     */
    public function findAllByUserIdsGrouped(array $userIds): ItemsInterface;
    
    /**
     * Returns all primary addresses for the user ids grouped by user id.
     *
     * @param array $userIds
     * @return ItemsInterface
     */
    public function findAllPrimaryByUserIdsGrouped(array $userIds): ItemsInterface;
}