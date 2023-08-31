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
use Tobento\App\User\Middleware\Authentication;
use Tobento\App\User\Authentication\Auth;
use Tobento\App\User\Authentication\Token\HeaderTransport;
use Tobento\App\User\Authentication\Token\InMemoryStorage;
use Tobento\App\User\Authenticator\TokenAuthenticator;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\Service\Clock\FrozenClock;
use Tobento\Service\Middleware\MiddlewareDispatcherInterface;
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Container\Container;
use Nyholm\Psr7\Factory\Psr17Factory;

class AuthenticationTest extends TestCase
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
    
    public function testHasNoAuthenticated()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $auth = new Auth();
        
        $md->add(new Authentication(
            auth: $auth,
            tokenTransport: new HeaderTransport(),
            tokenStorage: new InMemoryStorage(clock: new FrozenClock()),
            tokenAuthenticator: new TokenAuthenticator(
                userRepository: Factory::createUserRepository(),
            ),
        ));
        
        $request = (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');
        
        $request = $request->withHeader('X-Auth-Token', 'ID');

        $response = $md->handle($request);
        
        $this->assertFalse($auth->hasAuthenticated());
    }
    
    public function testHasAuthenticated()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $auth = new Auth();
        $tokenStorage = new InMemoryStorage(clock: new FrozenClock());
        $token = $tokenStorage->createToken(
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );
        
        $md->add(new Authentication(
            auth: $auth,
            tokenTransport: new HeaderTransport(),
            tokenStorage: $tokenStorage,
            tokenAuthenticator: new TokenAuthenticator(
                userRepository: Factory::createUserRepository(users: [
                    ['email' => 'tom@example.com'],
                ]),
            ),
        ));
        
        $request = (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');
        
        $request = $request->withHeader('X-Auth-Token', $token->id());

        $response = $md->handle($request);
        
        $this->assertTrue($auth->hasAuthenticated());
    }
    
    public function testThrowsIfAuthenticationFailsForSomeReason()
    {
        $this->expectException(AuthenticationException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $auth = new Auth();
        $tokenStorage = new InMemoryStorage(clock: new FrozenClock());
        $token = $tokenStorage->createToken(
            payload: ['userId' => 4],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedAt: null,
            expiresAt: null,
        );        
        
        $md->add(new Authentication(
            auth: $auth,
            tokenTransport: new HeaderTransport(),
            tokenStorage: $tokenStorage,
            tokenAuthenticator: new TokenAuthenticator(
                userRepository: Factory::createUserRepository(users: [
                    ['email' => 'tom@example.com'],
                ]),
            ),
        ));
        
        $request = (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');
        
        $request = $request->withHeader('X-Auth-Token', $token->id());

        $response = $md->handle($request);
    }
}