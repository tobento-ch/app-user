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

namespace Tobento\App\User\Authenticator;

use Tobento\App\User\UserInterface;
use Tobento\App\User\Exception\AuthenticationException;

/**
 * UserRoleVerifier
 */
final class UserRoleVerifier implements UserVerifierInterface
{
    /**
     * @var array<int, string>
     */
    private array $roles = [];
    
    /**
     * Create a new UserRoleVerifier.
     *
     * @param string ...$role The role(s) the user must have one of.
     */
    public function __construct(
        string ...$role,
    ) {
        $this->roles = $role;
    }
    
    /**
     * Verify user.
     *
     * @param UserInterface $user
     * @return void
     * @throws AuthenticationException If user verification fails.
     */
    public function verify(UserInterface $user): void
    {
        if (!in_array($user->role()->key(), $this->roles)) {
            throw new AuthenticationException('User has invalid role');
        }
    }
    
    /**
     * Returns the roles.
     *
     * @return array<int, string>
     */
    public function roles(): array
    {
        return $this->roles;
    }
}