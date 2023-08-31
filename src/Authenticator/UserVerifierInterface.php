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
 * UserVerifierInterface
 */
interface UserVerifierInterface
{
    /**
     * Verify user.
     *
     * @param UserInterface $user
     * @return void
     * @throws AuthenticationException If user verification fails.
     */
    public function verify(UserInterface $user): void;
}