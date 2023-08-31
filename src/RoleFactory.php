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

use Tobento\Service\Repository\Storage\EntityFactory;
use Tobento\Service\Acl\RoleInterface;

/**
 * RoleFactory
 */
class RoleFactory extends EntityFactory implements RoleFactoryInterface
{
    /**
     * Create an entity from array.
     *
     * @param array $attributes
     * @return RoleInterface The created entity.
     * @throws \Throwable If cannot create role
     */
    public function createEntityFromArray(array $attributes): RoleInterface
    {
        // Process the columns reading:
        $attributes = $this->columns->processReading($attributes);
        
        $permissions = $attributes['permissions'] ?? [];
        unset($attributes['permissions']);
        
        $role = new Role(...$attributes);
        $role->setPermissions($permissions);
        return $role;
    }
}