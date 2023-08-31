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

/**
 * PasswordHasherInterface
 */
interface PasswordHasherInterface
{
    /**
     * Hashes a plain password.
     *
     * @param string $plainPassword
     * @return string
     */
    public function hash(#[\SensitiveParameter] string $plainPassword): string;
    
    /**
     * Verifies a plain password against a hash.
     *
     * @param string $hashedPassword
     * @param string $plainPassword
     * @return bool
     */
    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword): bool;
}