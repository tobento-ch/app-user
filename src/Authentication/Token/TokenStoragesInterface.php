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

namespace Tobento\App\User\Authentication\Token;

use Tobento\App\User\Exception\TokenStorageException;

/**
 * TokenStoragesInterface
 */
interface TokenStoragesInterface
{
    /**
     * Add a token storage.
     *
     * @param TokenStorageInterface $storage
     * @return static $this
     */
    public function add(TokenStorageInterface $storage): static;
    
    /**
     * Register a token storage.
     *
     * @param string $name The token storage name.
     * @param callable $storage
     * @return static $this
     */
    public function register(string $name, callable $storage): static;
    
    /**
     * Returns the token storage by name.
     *
     * @param string $name The token storage name
     * @return TokenStorageInterface
     * @throws TokenStorageException
     */
    public function get(string $name): TokenStorageInterface;
    
    /**
     * Returns true if the token storage exists, otherwise false.
     *
     * @param string $name The token storage name.
     * @return bool
     */
    public function has(string $name): bool;
    
    /**
     * Returns all token storages names.
     *
     * @return array
     */
    public function names(): array;
}