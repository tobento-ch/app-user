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
use Tobento\App\User\Authentication\Token\TokenTransportInterface;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Auth;
use Tobento\App\User\Authentication\Authenticated;
use Tobento\App\User\Authenticator\TokenAuthenticatorInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\App\User\Exception\TokenNotFoundException;

/**
 * Authenticates the user by token if one exists.
 */
class Authentication implements MiddlewareInterface
{
    /**
     * Create a new Authentication.
     *
     * @param AuthInterface $auth
     * @param TokenTransportInterface $tokenTransport
     * @param TokenStorageInterface $tokenStorage
     * @param TokenAuthenticatorInterface $tokenAuthenticator
     */
    public function __construct(
        protected AuthInterface $auth,
        protected TokenTransportInterface $tokenTransport,
        protected TokenStorageInterface $tokenStorage,
        protected TokenAuthenticatorInterface $tokenAuthenticator,
    ) {}
    
    /**
     * Process the middleware.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws AuthenticationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Add auth to the request:
        $request = $request->withAttribute(AuthInterface::class, $this->auth);
        
        // Handle token:
        $tokenId = $this->tokenTransport->fetchTokenId($request);

        if (!is_null($tokenId)) {
            try {
                $token = $this->tokenStorage->fetchToken($tokenId);
                $user = $this->tokenAuthenticator->authenticate($token);
                
                $this->auth->start(
                    new Authenticated(token: $token, user: $user),
                    $this->tokenTransport->name()
                );
            } catch (TokenNotFoundException $e) {
                // ignore TokenNotFoundException as to
                // proceed with handling the response.
                // other exceptions will be handled by the error handler!
            }
        }
        
        // Handle the response:
        $response = $handler->handle($request);
        
        if ($this->auth->isClosed() && $this->auth->getUnauthenticated()) {
            
            $this->tokenStorage->deleteToken($this->auth->getUnauthenticated()->token());

            return $this->tokenTransport->removeToken(
                token: $this->auth->getUnauthenticated()->token(),
                request: $request,
                response: $response,
            );
        }
        
        if (! $this->auth->hasAuthenticated()) {
            return $response;
        }
        
        return $this->tokenTransport->commitToken(
            token: $this->auth->getAuthenticated()->token(),
            request: $request,
            response: $response,
        );
    }
}