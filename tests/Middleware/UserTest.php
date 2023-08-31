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

namespace Tobento\App\Test\Boot\Middleware;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Test\Factory;
use Tobento\App\User\Middleware\User as UserMiddleware;
use Tobento\App\User\UserInterface;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Auth;
use Tobento\App\User\Authentication\Authenticated;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\User;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Acl\Acl;
use Tobento\Service\Middleware\MiddlewareDispatcherInterface;
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Container\Container;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use DateTimeImmutable;

class UserTest extends TestCase
{
    private function createMiddlewareDispatcher(): MiddlewareDispatcherInterface
    {
        // create response
        $response = (new Psr17Factory())->createResponse(404);

        // create middlware dispatcher
        return new MiddlewareDispatcher(
            new FallbackHandler($response),
            new AutowiringMiddlewareFactory(new Container())
        );
    }
    
    public function testIsGuestUserIfNotAuthenticated()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new UserMiddleware(
            userFactory: Factory::createUserFactory(),
            acl: null,
        ));
        
        $md->add(function($request, $handler): ResponseInterface {
            $roleKey = $request->getAttribute(UserInterface::class)?->getRoleKey();
            $response = $handler->handle($request);
            $response->getBody()->write($roleKey);
            return $response;
        });
        
        $request = (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');

        $response = $md->handle($request);
        
        $this->assertSame('guest', (string)$response->getBody());
    }
    
    public function testIsAuthenticatedUser()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new UserMiddleware(
            userFactory: Factory::createUserFactory(),
            acl: null,
        ));
        
        $md->add(function($request, $handler): ResponseInterface {
            $userId = $request->getAttribute(UserInterface::class)?->id();
            $response = $handler->handle($request);
            $response->getBody()->write((string)$userId);
            return $response;
        });
        
        $request = (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');

        $auth = new Auth();
        $auth->start(authenticated: new Authenticated(
            token: new Token(
                id: 'id',
                payload: [],
                authenticatedVia: 'via',
                authenticatedBy: 'by',
                issuedBy: 'storageName',
                issuedAt: new DateTimeImmutable('now'),
            ),
            user: new User(id: 55),
        ));
        
        $request = $request->withAttribute(AuthInterface::class, $auth);
        
        $response = $md->handle($request);
        
        $this->assertSame('55', (string)$response->getBody());
    }
    
    public function testCurrentUserIsSetOnAclIfSpecified()
    {
        $acl = new Acl();
        
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new UserMiddleware(
            userFactory: Factory::createUserFactory(),
            acl: $acl,
        ));
        
        $md->add(function($request, $handler) use ($acl): ResponseInterface {
            $roleKey = $acl->getCurrentUser()?->getRoleKey();
            $response = $handler->handle($request);
            $response->getBody()->write($roleKey);
            return $response;
        });
        
        $request = (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');

        $response = $md->handle($request);
        
        $this->assertSame('guest', (string)$response->getBody());
    }
}