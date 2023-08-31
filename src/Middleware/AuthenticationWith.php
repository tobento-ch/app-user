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
use Tobento\App\User\Authentication\Token\TokenTransportsInterface;
use Tobento\App\User\Authentication\Token\TokenStoragesInterface;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authenticator\TokenAuthenticatorInterface;
use Tobento\App\User\Exception\AuthenticationException;

/**
 * Authenticates the user by token if one exists with specific token transport and storage.
 */
class AuthenticationWith implements MiddlewareInterface
{
    protected Authentication $authentication;
    
    /**
     * Create a new AuthenticationWith.
     *
     * @param AuthInterface $auth
     * @param TokenTransportsInterface $tokenTransports
     * @param TokenStoragesInterface $tokenStorages
     * @param TokenAuthenticatorInterface $tokenAuthenticator
     * @param string $transportName
     * @param string $storageName
     */
    public function __construct(
        protected AuthInterface $auth,
        protected TokenTransportsInterface $tokenTransports,
        protected TokenStoragesInterface $tokenStorages,
        protected TokenAuthenticatorInterface $tokenAuthenticator,
        protected string $transportName,
        protected string $storageName,
    ) {
        $this->authentication = new Authentication(
            auth: $auth,
            tokenTransport: $tokenTransports->get($transportName),
            tokenStorage: $tokenStorages->get($storageName),
            tokenAuthenticator: $tokenAuthenticator,
        );
    }
    
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
        return $this->authentication->process($request, $handler);
    }
}