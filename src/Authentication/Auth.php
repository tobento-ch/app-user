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

namespace Tobento\App\User\Authentication;

use Psr\EventDispatcher\EventDispatcherInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\App\User\Event;

/**
 * Auth
 */
class Auth implements AuthInterface
{
    /**
     * Create a new Auth.
     *
     * @param null|EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected null|EventDispatcherInterface $eventDispatcher = null,
    ) {}
    
    /**
     * @var null|AuthenticatedInterface
     */
    protected null|AuthenticatedInterface $authenticated = null;
    
    /**
     * @var bool
     */
    protected bool $closed = false;
    
    /**
     * Start authentication.
     *
     * @param AuthenticatedInterface $authenticated
     * @param null|string $tokenTransportName
     * @return void
     */
    public function start(AuthenticatedInterface $authenticated, null|string $tokenTransportName = null): void
    {
        $this->authenticated = $authenticated;
        $this->authenticated->user()->setAuthenticated(true);
        $this->closed = false;
        $this->eventDispatcher?->dispatch(new Event\Authenticated($this->authenticated));
    }
    
    /**
     * Close authentication.
     *
     * @return void
     * @throws AuthenticationException
     */
    public function close(): void
    {
        $this->closed = true;
        
        if ($this->hasAuthenticated()) {
            $this->getAuthenticated()->user()->setAuthenticated(false);
            $this->eventDispatcher?->dispatch(new Event\Unauthenticated($this->getAuthenticated()));
            $this->authenticated = null;
        }
    }
    
    /**
     * Returns true if authentication is closed, otherwise true.
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }
    
    /**
     * Returns the token transport name.
     *
     * @return null|string
     */
    public function getTokenTransportName(): null|string
    {
        return null;
    }
    
    /**
     * Returns true if has authenticated, otherwise false.
     *
     * @return bool
     */
    public function hasAuthenticated(): bool
    {
        return is_null($this->getAuthenticated()) ? false : true;
    }
    
    /**
     * Returns the authenticated or null if none.
     *
     * @return null|AuthenticatedInterface
     */
    public function getAuthenticated(): null|AuthenticatedInterface
    {
        return $this->authenticated;
    }
}