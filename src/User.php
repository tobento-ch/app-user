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
     * @var array<string, string> The verified channels.
     */
    protected array $verified = [];
    
    /**
     * @var array<string, mixed>
     */
    protected array $settings = [];
    
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
     * Set the verified channels.
     *
     * @param array<string, string> $verified
     * @return static $this
     */
    public function setVerified(array $verified): static
    {
        $this->verified = $verified;
        return $this;
    }
    
    /**
     * Returns all verified channels.
     *
     * @return array<string, string>
     */
    public function getVerified(): array
    {
        return $this->verified;
    }
    
    /**
     * Returns the verified at date for the specified channel.
     *
     * @return null|string
     */
    public function getVerifiedAt(string $channel): null|string
    {
        return $this->getVerified()[$channel] ?? null;
    }
    
    /**
     * Returns true if the specified channels are verified, otherwise false.
     *
     * @param array $channels The channels (email, smartphone e.g).
     * @return bool
     */
    public function isVerified(array $channels): bool
    {
        if (empty($channels)) {
            return false;
        }
        
        $verified = array_keys($this->getVerified());
        
        foreach($channels as $channel) {
            if (!in_array($channel, $verified)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Returns true if at least one channel is verified, otherwise false.
     *
     * @param null|array $channels At least one of the channels must be verified.
     * @return bool
     */
    public function isOneVerified(null|array $channels = null): bool
    {
        if (is_null($channels)) {
            return !empty($this->getVerified());
        }
        
        $verified = array_keys($this->getVerified());
        
        foreach($channels as $channel) {
            if (in_array($channel, $verified)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sets the user settings.
     *
     * @param array<string, mixed> $settings
     * @return static $this
     */
    public function setSettings(array $settings): static
    {
        $this->settings = $settings;
        return $this;
    }
    
    /**
     * Returns a setting value by name.
     *
     * @param string $name
     * @return mixed
     */
    public function setting(string $name, mixed $default): mixed
    {
        return $this->settings[$name] ?? $default;
    }

    /**
     * Object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $user = parent::toArray();
        $user['isAuthenticated'] = $this->isAuthenticated();
        $user['verified'] = $this->getVerified();
        $user['settings'] = $this->settings;

        if ($this->hasRole()) {
            $role = $this->role();
            $user['role_key'] = $role->key();
            $user['role'] = [
                'key' => $role->key(),
                'name' => $role->name(),
                'active' => $role->active(),
                'areas' => $role->areas(),
                'permissions' => $role->getPermissions(),
            ];
        }
        
        return $user;
    }
}