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

use Tobento\App\User\Exception\TokenTransportException;

/**
 * TokenTransportsInterface
 */
interface TokenTransportsInterface
{
    /**
     * Add a token transport.
     *
     * @param TokenTransportInterface $transport
     * @return static $this
     */
    public function add(TokenTransportInterface $transport): static;
    
    /**
     * Register a token transport.
     *
     * @param string $name The token transport name.
     * @param callable $transport
     * @return static $this
     */    
    public function register(string $name, callable $transport): static;
    
    /**
     * Returns true if the token transport exists, otherwise false.
     *
     * @param string $name The token transport name.
     * @return bool
     */    
    public function has(string $name): bool;
    
    /**
     * Returns the token transport by name.
     *
     * @param string $name The token transport name
     * @return TokenTransportInterface
     * @throws TokenTransportException
     */    
    public function get(string $name): TokenTransportInterface;
    
    /**
     * Returns a new instance with the specified transports.
     *
     * @param array $names The token transport names
     * @return TokenTransportInterface
     * @throws TokenTransportException
     */    
    public function only(array $names): TokenTransportInterface;
}