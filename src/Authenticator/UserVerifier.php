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

use Closure;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\App\User\UserInterface;

/**
 * UserVerifier
 */
final class UserVerifier implements UserVerifierInterface
{
    /**
     * Create a new UserVerifier.
     *
     * @param bool|Closure $verified
     * @param string $message
     */
    public function __construct(
        private bool|Closure $verified,
        private string $message = '',
    ) {
        $this->verified = $verified;
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
        if (is_bool($this->verified) && $this->verified === false) {
            throw new AuthenticationException($this->message);
        }
        
        if (is_callable($this->verified)) {
            $verified = call_user_func($this->verified, $user);
            
            if ($verified === false) {
                throw new AuthenticationException($this->message);
            }
        }        
    }
}