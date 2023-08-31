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
use Throwable;

/**
 * TokenStorages
 */
class TokenStorages implements TokenStoragesInterface
{
    /**
     * @var array<string, callable|TokenStorageInterface>
     */
    protected array $storages = [];
    
    /**
     * Create a new TokenStorages.
     *
     * @param TokenStorageInterface ...$storages
     */
    public function __construct(
        TokenStorageInterface ...$storages,
    ) {
        foreach($storages as $storage) {
            $this->add($storage);
        }
    }
    
    /**
     * Add a token storage.
     *
     * @param TokenStorageInterface $storage
     * @return static $this
     */
    public function add(TokenStorageInterface $storage): static
    {
        $this->storages[$storage->name()] = $storage;
        return $this;
    }
    
    /**
     * Register a token storage.
     *
     * @param string $name The token storage name.
     * @param callable $storage
     * @return static $this
     */    
    public function register(string $name, callable $storage): static
    {
        $this->storages[$name] = $storage;
        return $this;
    }
    
    /**
     * Returns the token storage by name.
     *
     * @param string $name The token storage name
     * @return TokenStorageInterface
     * @throws TokenStorageException
     */    
    public function get(string $name): TokenStorageInterface
    {
        if (!$this->has($name)) {
            throw new TokenStorageException(sprintf('Storage [%s] not found!', $name));
        }
        
        if (! $this->storages[$name] instanceof TokenStorageInterface) {
            try {
                $this->storages[$name] = $this->createStorage($name, $this->storages[$name]);
            } catch(Throwable $e) {
                throw new TokenStorageException($e->getMessage(), (int)$e->getCode(), $e);
            }
        }
        
        return $this->storages[$name];
    }
    
    /**
     * Returns true if the token storage exists, otherwise false.
     *
     * @param string $name The token storage name.
     * @return bool
     */    
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->storages);
    }
    
    /**
     * Create a new token storage.
     *
     * @param string $name
     * @param callable $factory
     * @return TokenStorageInterface
     */
    protected function createStorage(string $name, callable $factory): TokenStorageInterface
    {
        return call_user_func_array($factory, [$name]);
    }
}