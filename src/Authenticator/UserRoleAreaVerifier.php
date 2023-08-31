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
 * UserRoleAreaVerifier
 */
final class UserRoleAreaVerifier implements UserVerifierInterface
{
    /**
     * @var array<int, string>
     */
    private array $areas = [];
    
    /**
     * Create a new UserRoleAreaVerifier.
     *
     * @param string ...$area The role area(s) the user must have one of.
     */
    public function __construct(
        string ...$area,
    ) {
        $this->areas = $area;
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
        foreach($user->role()->areas() as $area) {
            if (in_array($area, $this->areas)) {
                return;
            }
        }
        
        throw new AuthenticationException('User has invalid role area');
    }
    
    /**
     * Returns the areas.
     *
     * @return array<int, string>
     */
    public function areas(): array
    {
        return $this->areas;
    }
}