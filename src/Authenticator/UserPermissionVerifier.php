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
 * UserPermissionVerifier
 */
final class UserPermissionVerifier implements UserVerifierInterface
{
    /**
     * @var string
     */
    private string $permission;
    
    /**
     * Create a new UserPermissionVerifier.
     *
     * @param string $permission The permission the user must have.
     */
    public function __construct(
        string $permission,
    ) {
        $this->permission = $permission;
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
        if ($user->cant($this->permission)) {
            throw new AuthenticationException(
                message: sprintf('User don\'t have a required "%s" permission.', $this->permission),
            );
        }
    }
    
    /**
     * Returns the permission.
     *
     * @return string
     */
    public function permission(): string
    {
        return $this->permission;
    }
}