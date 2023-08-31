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
 * VerifyPermission
 */
class VerifyPermission implements MiddlewareInterface
{
    /**
     * Create a new VerifyPermission.
     *
     * @param AclInterface $acl
     * @param null|string $permission
     * @param string $message
     * @param string $messageLevel
     * @param null|string $redirectUri
     * @param null|string $redirectRoute
     */
    public function __construct(
        protected AclInterface $acl,
        protected null|string $permission = null,
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
        if (is_null($this->permission)) {
            if (is_string($request->getAttribute('route.name'))) {
                $this->permission = $request->getAttribute('route.name');
            }
            
            if (is_string($request->getAttribute('route.can'))) {
                $this->permission = $request->getAttribute('route.can');
            }
        }
        
        if (is_null($this->permission)) {
            throw new PermissionDeniedException(
                permission: '',
                message: 'No permission specified.',
            );
        }
        
        if ($this->acl->cant($this->permission)) {
            throw new PermissionDeniedException(
                permission: $this->permission,
                message: $this->message,
                messageLevel: $this->messageLevel,
                redirectUri: $this->redirectUri,
                redirectRoute: $this->redirectRoute,
            );
        }
        
        return $handler->handle($request);
    }
}