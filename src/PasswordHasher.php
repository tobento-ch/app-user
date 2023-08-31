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
 * PasswordHasher
 */
class PasswordHasher implements PasswordHasherInterface
{
    /**
     * Create a new PasswordHasher.
     *
     * @param string|int|null $algo
     * @param array $options
     */
    public function __construct(
        protected string|int|null $algo = \PASSWORD_DEFAULT,
        protected array $options = [],
    ) {}
    
    /**
     * Hashes a plain password.
     *
     * @param string $plainPassword
     * @return string
     */
    public function hash(#[\SensitiveParameter] string $plainPassword): string
    {
        return password_hash($plainPassword, $this->algo, $this->options);
    }

    /**
     * Verifies a plain password against a hash.
     *
     * @param string $hashedPassword
     * @param string $plainPassword
     * @return bool
     */
    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}