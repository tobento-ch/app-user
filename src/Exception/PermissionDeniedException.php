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

use Throwable;

/**
 * PermissionDeniedException
 */
class PermissionDeniedException extends AuthorizationException
{
    /**
     * Create a new PermissionDeniedException.
     *
     * @param string $permission
     * @param string $message
     * @param string $messageLevel
     * @param null|string $redirectUri
     * @param null|string $redirectRoute
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        protected string $permission,
        protected string $userMessage = '',
        protected string $messageLevel = '',
        protected null|string $redirectUri = null,
        protected null|string $redirectRoute = null,
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        if ($message === '') {
            $message = sprintf('You don\'t have a required "%s" permission.', $this->permission);    
        }
        
        parent::__construct(
            message: $message,
            messageLevel: $messageLevel,
            redirectUri: $redirectUri,
            redirectRoute: $redirectRoute,
            code: $code,
            previous: $previous,
        );
    }
    
    /**
     * Returns the permission.
     *
     * @return string
     */
    public function permission(): string
    {
        return $this->permission;
    }
}