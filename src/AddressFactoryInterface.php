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
use Tobento\Service\User\AddressesInterface;

/**
 * AddressFactoryInterface
 */
interface AddressFactoryInterface extends StorageEntityFactoryInterface
{
    /**
     * Create addresses.
     *
     * @param mixed $addresses
     * @return AddressesInterface
     */
    public function createAddresses(mixed $addresses): AddressesInterface;
}