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
use Tobento\App\User\UserInterface;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Exception\AuthorizationException;

/**
 * Protects routes from unverified users:
 */
class Verified implements MiddlewareInterface
{
    /**
     * Create a new Verified.
     *
     * @param null|string $oneOf e.g 'email|smartphone' or null if unspecified
     * @param null|string $allOf If channels are specified those are used.
     * @param string $message
     * @param string $messageLevel
     * @param null|string $redirectUri
     * @param null|string $redirectRoute
     */
    public function __construct(
        protected null|string $oneOf = null,
        protected null|string $allOf = null,
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
        $user = $auth?->getAuthenticated()?->user();

        if (
            is_null($user)
            || !$this->isVerified($user)
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
     * Returns true if the user is verified, otherwise false.
     *
     * @param UserInterface $user
     * @return bool
     */
    protected function isVerified(UserInterface $user): bool
    {
        if (!is_null($this->allOf)) {
            return $user->isVerified(channels: explode('|', $this->allOf));
        }
        
        if (!is_null($this->oneOf)) {
            return $user->isOneVerified(channels: explode('|', $this->oneOf));
        }
        
        return $user->isOneVerified();
    }
}