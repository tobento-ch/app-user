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

use Tobento\Service\User\User as BaseUser;
use Tobento\Service\Acl\Authorizable;
use Tobento\Service\Acl\AuthorizableAware;

/**
 * User
 */
class User extends BaseUser implements UserInterface, Authorizable
{
    use AuthorizableAware;

    /**
     * @var bool
     */
    protected bool $isAuthenticated = false;

    /**
     * Set if the user is authenticated.
     *
     * @param bool $isAuthenticated
     * @return static $this
     */
    public function setAuthenticated(bool $isAuthenticated): static
    {
        $this->isAuthenticated = $isAuthenticated;
        return $this;
    }
    
    /**
     * Returns whether the user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->isAuthenticated;
    }
    
    /**
     * Object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $role = $this->role();
        
        $user = parent::toArray();
        $user['isAuthenticated'] = $this->isAuthenticated();
        $user['role'] = [
            'key' => $role->key(),
            'name' => $role->name(),
            'active' => $role->active(),
            'areas' => $role->areas(),
            'permissions' => $role->getPermissions(),
        ];
        
        return $user;
    }
}