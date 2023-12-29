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

use Tobento\App\User\Exception\AuthenticationException;

/**
 * AuthInterface
 */
interface AuthInterface
{
    /**
     * Start authentication.
     *
     * @param AuthenticatedInterface $authenticated
     * @param null|string $tokenTransportName
     * @return void
     */
    public function start(AuthenticatedInterface $authenticated, null|string $tokenTransportName = null): void;
    
    /**
     * Close authentication.
     *
     * @return void
     * @throws AuthenticationException
     */
    public function close(): void;
    
    /**
     * Returns true if authentication is closed, otherwise true.
     *
     * @return bool
     */
    public function isClosed(): bool;
    
    /**
     * Returns the token transport name.
     *
     * @return null|string
     */
    public function getTokenTransportName(): null|string;
    
    /**
     * Returns true if has authenticated, otherwise false.
     *
     * @return bool
     */
    public function hasAuthenticated(): bool;
    
    /**
     * Returns the authenticated or null if none.
     *
     * @return null|AuthenticatedInterface
     */
    public function getAuthenticated(): null|AuthenticatedInterface;
    
    /**
     * Returns the unauthenticated or null if none.
     *
     * @return null|AuthenticatedInterface
     */
    public function getUnauthenticated(): null|AuthenticatedInterface;
}