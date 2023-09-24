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

use Tobento\Service\User\UserInterface as BaseUserInterface;
use Tobento\Service\Acl\Authorizable;

/**
 * UserInterface
 */
interface UserInterface extends BaseUserInterface, Authorizable
{
    /**
     * Set if the user is authenticated.
     *
     * @param bool $isAuthenticated
     * @return static $this
     */
    public function setAuthenticated(bool $isAuthenticated): static;
    
    /**
     * Returns whether the user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool;
    
    /**
     * Set the verified channels.
     *
     * @param array<string, string> $verified
     * @return static $this
     */
    public function setVerified(array $verified): static;
    
    /**
     * Returns all verified channels.
     *
     * @return array<string, string>
     */
    public function getVerified(): array;
    
    /**
     * Returns the verified at date for the specified channel.
     *
     * @return null|string
     */
    public function getVerifiedAt(string $channel): null|string;
    
    /**
     * Returns true if the specified channels are verified, otherwise false.
     *
     * @param array $channels The channels (email, smartphone e.g).
     * @return bool
     */
    public function isVerified(array $channels): bool;
    
    /**
     * Returns true if at least one channel is verified, otherwise false.
     *
     * @param null|array $channels At least one of the channels must be verified.
     * @return bool
     */
    public function isOneVerified(null|array $channels = null): bool;
}