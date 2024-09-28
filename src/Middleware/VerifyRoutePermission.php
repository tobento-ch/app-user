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
use Tobento\App\User\Exception\PermissionDeniedException;

/**
 * VerifyRoutePermission
 */
class VerifyRoutePermission implements MiddlewareInterface
{
    /**
     * Create a new VerifyRoutePermission.
     *
     * @param AclInterface $acl
     * @param array<string, string> $permissions E.g ['route.name' => 'permission']
     * @param string $message
     * @param string $messageLevel
     * @param null|string $redirectUri
     * @param null|string $redirectRoute
     */
    public function __construct(
        protected AclInterface $acl,
        protected array $permissions = [],
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
     * @throws PermissionDeniedException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeName = $request->getAttribute('route.name');
        
        if (!is_string($routeName)) {
            throw new PermissionDeniedException(
                permission: '',
                message: $this->message,
                messageLevel: $this->messageLevel,
                redirectUri: $this->redirectUri,
                redirectRoute: $this->redirectRoute,
                reason: 'No route name found to determine permission',
            );
        }
        
        $permission = $this->permissions[$routeName] ?? null;
        
        if (!is_string($permission)) {
            throw new PermissionDeniedException(
                permission: '',
                message: $this->message,
                messageLevel: $this->messageLevel,
                redirectUri: $this->redirectUri,
                redirectRoute: $this->redirectRoute,
                reason: sprintf('No permission specified for the route %s', $routeName),
            );
        }
        
        if ($this->acl->cant($permission)) {
            throw new PermissionDeniedException(
                permission: $permission,
                message: $this->message,
                messageLevel: $this->messageLevel,
                redirectUri: $this->redirectUri,
                redirectRoute: $this->redirectRoute,
            );
        }
        
        return $handler->handle($request);
    }
}