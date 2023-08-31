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
 * Protects routes from authenticated users.
 */
class Unauthenticated implements MiddlewareInterface
{
    /**
     * Create a new Unauthenticated.
     *
     * @param string $message
     * @param string $messageLevel
     * @param null|string $redirectUri
     * @param null|string $redirectRoute
     */
    public function __construct(
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

        if (! $auth?->hasAuthenticated()) {
            return $handler->handle($request);
        }
        
        throw new AuthorizationException(
            message: $this->message,
            messageLevel: $this->messageLevel,
            redirectUri: $this->redirectUri,
            redirectRoute: $this->redirectRoute,
        );
    }
}