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
use Tobento\Service\Acl\RoleInterface;

/**
 * RoleResourceInterface
 */
interface RoleRepositoryInterface extends RepositoryInterface
{
    /**
     * Returns the found role using the specified key
     * or null if none found.
     *
     * @param string $key The key such as 'editor'
     * @return null|RoleInterface
     */
    public function findByKey(string $key): null|RoleInterface;
}