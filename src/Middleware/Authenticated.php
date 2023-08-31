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
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\AuthenticatedInterface;
use Tobento\App\User\Exception\AuthorizationException;

/**
 * Protects routes from unauthenticated users.
 */
class Authenticated implements MiddlewareInterface
{
    /**
     * Create a new Authenticated.
     *
     * @param null|string $via
     * @param null|string $exceptVia
     * @param string $message
     * @param string $messageLevel
     * @param null|string $redirectUri
     * @param null|string $redirectRoute
     */
    public function __construct(
        protected null|string $via = null,
        protected null|string $exceptVia = null,
        protected string $message = '',
        protected string $messageLevel = 'notice',
        protected null|string $redirectUri = null,
        protected null|string $redirectRoute = null,
    ) {}
    
    /**
     * Process the middleware.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws AuthorizationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $auth = $request->getAttribute(AuthInterface::class);
        $authenticated = $auth?->getAuthenticated();

        if (
            is_null($authenticated)
            || !$this->isAuthorized($authenticated)
        ) {
            throw new AuthorizationException(
                message: $this->message,
                messageLevel: $this->messageLevel,
                redirectUri: $this->redirectUri,
                redirectRoute: $this->redirectRoute,
            );
        }
        
        return $handler->handle($request);
    }
    
    /**
     * Returns true if the authenticated is authorized otherwise false.
     *
     * @param AuthenticatedInterface $authenticated
     * @return bool
     */
    protected function isAuthorized(AuthenticatedInterface $authenticated): bool
    {
        if (!is_null($this->via)) {
            return in_array($authenticated->via(), explode('|', $this->via));
        }
        
        if (!is_null($this->exceptVia)) {
            return !in_array($authenticated->via(), explode('|', $this->exceptVia));
        }        
        
        return true;
    }
}