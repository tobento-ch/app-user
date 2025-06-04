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

use Tobento\Service\Acl\Role as BaseRole;

/**
 * Role
 */
class Role extends BaseRole implements RoleInterface
{
    /**
     * Create a new Role
     *
     * @param string|int $id,
     * @param string $key A role key such as 'editor'.
     * @param array $areas The areas for the role ['frontend', 'api'].
     * @param bool $active If the role is active.
     * @param null|string $name A role name such as 'Editor'.
     */    
    public function __construct(
        protected string|int $id,
        protected string $key,
        protected array $areas = ['frontend'],
        protected bool $active = true,
        protected null|string $name = null,
    ) {}
    
    /**
     * Get the id.
     *
     * @return int|string
     */
    public function id(): string|int
    {
        return $this->id;
    }
    
    /**
     * Object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'key' => $this->key(),
            'name' => $this->name(),
            'active' => $this->active(),
            'areas' => $this->areas(),
            'permissions' => $this->getPermissions(),
        ];
    }
}