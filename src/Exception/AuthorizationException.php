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

namespace Tobento\App\User\Exception;

use RuntimeException;
use Throwable;

/**
 * AuthorizationException
 */
class AuthorizationException extends RuntimeException
{
    /**
     * Create a new AuthorizationException.
     *
     * @param string $message
     * @param string $messageLevel
     * @param null|string $redirectUri
     * @param null|string $redirectRoute     
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        string $message = 'Unauthorized.',
        protected string $messageLevel = '',
        protected null|string $redirectUri = null,
        protected null|string $redirectRoute = null,        
        int $code = 0,
        null|Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Returns the message level.
     *
     * @return string
     */
    public function messageLevel(): string
    {
        return $this->messageLevel ?: 'notice';
    }
    
    /**
     * Returns the redirect uri.
     *
     * @return null|string
     */
    public function redirectUri(): null|string
    {
        return $this->redirectUri;
    }
    
    /**
     * Returns the redirect route.
     *
     * @return null|string
     */
    public function redirectRoute(): null|string
    {
        return $this->redirectRoute;
    }
}