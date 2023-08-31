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
use Tobento\App\User\UserFactoryInterface;
use Tobento\App\User\UserInterface;
use Tobento\Service\Acl\AclInterface;

/**
 * If no user exists, it creates a guest user and sets the user request attribute.
 * Furthermore, it sets the current acl user.
 */
class User implements MiddlewareInterface
{
    /**
     * Create a new User.
     *
     * @param UserFactoryInterface $userFactory
     * @param null|AclInterface $acl
     */
    public function __construct(
        protected UserFactoryInterface $userFactory,
        protected null|AclInterface $acl,
    ) {}
    
    /**
     * Process the middleware.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get the authenticated user if exists:
        $user = $request->getAttribute(AuthInterface::class)?->getAuthenticated()?->user();
        
        // Otherwise, create guest user:
        if (is_null($user)) {
            $user = $this->userFactory->createGuestUser();
        }
        
        $request = $request->withAttribute(UserInterface::class, $user);

        // Set user on acl:
        if ($this->acl) {
            $this->acl->setCurrentUser($user);
        }
        
        return $handler->handle($request);
    }
}