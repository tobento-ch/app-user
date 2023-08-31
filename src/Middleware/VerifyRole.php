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

namespace Tobento\App\User\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tobento\Service\Acl\AclInterface;
use Tobento\App\User\Exception\RoleDeniedException;

/**
 * VerifyRole
 */
class VerifyRole implements MiddlewareInterface
{
    /**
     * Create a new VerifyRole.
     *
     * @param AclInterface $acl
     * @param string $role
     * @param string $message
     * @param string $messageLevel
     * @param null|string $redirectUri
     * @param null|string $redirectRoute
     */
    public function __construct(
        protected AclInterface $acl,
        protected string $role,
        protected string $message = '',
        protected string $messageLevel = '',
        protected null|string $redirectUri = null,
        protected null|string $redirectRoute = null,
    ) {}
    
    /**
     * Process the middleware.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws RoleDeniedException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->acl->getCurrentUser();
        
        if (is_null($user) || !$user->hasRole()) {
            throw new RoleDeniedException(
                role: $this->role,
                message: $this->message,
                messageLevel: $this->messageLevel,
                redirectUri: $this->redirectUri,
                redirectRoute: $this->redirectRoute,
            );
        }
        
        $roles = explode('|', $this->role);
        
        if (in_array($user->role()->key(), $roles)) {
            return $handler->handle($request);
        }

        throw new RoleDeniedException(
            role: $this->role,
            message: $this->message,
            messageLevel: $this->messageLevel,
            redirectUri: $this->redirectUri,
            redirectRoute: $this->redirectRoute,
        );
    }
}