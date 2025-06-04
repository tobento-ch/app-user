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

use Tobento\Service\Acl\RoleInterface as BaseRoleInterface;

/**
 * RoleInterface
 */
interface RoleInterface extends BaseRoleInterface
{
    /**
     * Get the id.
     *
     * @return int|string
     */
    public function id(): string|int;
    
    /**
     * Object to array
     *
     * @return array
     */
    public function toArray(): array;
}