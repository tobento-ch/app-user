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
use Tobento\App\User\Middleware\Verified;
use Tobento\App\User\Authentication\Auth;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Authenticated as AuthenticatedUser;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\User;
use Tobento\App\User\Exception\AuthorizationException;
use Tobento\Service\Middleware\MiddlewareDispatcherInterface;
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Container\Container;
use Nyholm\Psr7\Factory\Psr17Factory;
use DateTimeImmutable;

class VerifiedTest extends TestCase
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
    
    public function testIsVerified()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new Verified());
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $authenticated = new AuthenticatedUser(
            token: new Token(
                id: 'ID',
                payload: [],
                authenticatedVia: 'via',
                authenticatedBy: null,
                issuedBy: 'storage',
                issuedAt: new DateTimeImmutable('now'),
            ),
            user: (new User(id: 1))->setVerified(['email' => '2023-09-24 00:00:00']),
        );
        
        $auth = new Auth();
        $auth->start(authenticated: $authenticated);
        
        $request = $request->withAttribute(AuthInterface::class, $auth);

        $response = $md->handle($request);
        
        $this->assertTrue(true);
    }
    
    public function testIsVerifiedFailsIfNoChannelIsVerifiedAtAll()
    {
        $this->expectException(AuthorizationException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new Verified());
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $authenticated = new AuthenticatedUser(
            token: new Token(
                id: 'ID',
                payload: [],
                authenticatedVia: 'via',
                authenticatedBy: null,
                issuedBy: 'storage',
                issuedAt: new DateTimeImmutable('now'),
            ),
            user: (new User(id: 1))->setVerified([]),
        );
        
        $auth = new Auth();
        $auth->start(authenticated: $authenticated);
        
        $request = $request->withAttribute(AuthInterface::class, $auth);

        $response = $md->handle($request);
    }
    
    public function testIsVerifiedOneOf()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new Verified(oneOf: 'email|smartphone'));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $authenticated = new AuthenticatedUser(
            token: new Token(
                id: 'ID',
                payload: [],
                authenticatedVia: 'via',
                authenticatedBy: null,
                issuedBy: 'storage',
                issuedAt: new DateTimeImmutable('now'),
            ),
            user: (new User(id: 1))->setVerified(['email' => '2023-09-24 00:00:00']),
        );
        
        $auth = new Auth();
        $auth->start(authenticated: $authenticated);
        
        $request = $request->withAttribute(AuthInterface::class, $auth);

        $response = $md->handle($request);
        
        $this->assertTrue(true);
    }
    
    public function testIsVerifiedOneOfFailsIfNoChannelIsVerifiedAtAll()
    {
        $this->expectException(AuthorizationException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new Verified(oneOf: 'email|smartphone'));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $authenticated = new AuthenticatedUser(
            token: new Token(
                id: 'ID',
                payload: [],
                authenticatedVia: 'via',
                authenticatedBy: null,
                issuedBy: 'storage',
                issuedAt: new DateTimeImmutable('now'),
            ),
            user: (new User(id: 1))->setVerified([]),
        );
        
        $auth = new Auth();
        $auth->start(authenticated: $authenticated);
        
        $request = $request->withAttribute(AuthInterface::class, $auth);

        $response = $md->handle($request);
    }
    
    public function testIsVerifiedAllOf()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new Verified(allOf: 'email|smartphone'));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $authenticated = new AuthenticatedUser(
            token: new Token(
                id: 'ID',
                payload: [],
                authenticatedVia: 'via',
                authenticatedBy: null,
                issuedBy: 'storage',
                issuedAt: new DateTimeImmutable('now'),
            ),
            user: (new User(id: 1))->setVerified([
                'email' => '2023-09-24 00:00:00',
                'smartphone' => '2024-09-24 00:00:00',
            ]),
        );
        
        $auth = new Auth();
        $auth->start(authenticated: $authenticated);
        
        $request = $request->withAttribute(AuthInterface::class, $auth);

        $response = $md->handle($request);
        
        $this->assertTrue(true);
    }
    
    public function testIsVerifiedAllOfFailsIfOneChannelIsNotVerified()
    {
        $this->expectException(AuthorizationException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new Verified(allOf: 'email|smartphone'));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $authenticated = new AuthenticatedUser(
            token: new Token(
                id: 'ID',
                payload: [],
                authenticatedVia: 'via',
                authenticatedBy: null,
                issuedBy: 'storage',
                issuedAt: new DateTimeImmutable('now'),
            ),
            user: (new User(id: 1))->setVerified([
                'email' => '2023-09-24 00:00:00',
            ]),
        );
        
        $auth = new Auth();
        $auth->start(authenticated: $authenticated);
        
        $request = $request->withAttribute(AuthInterface::class, $auth);

        $response = $md->handle($request);
    }

    public function estFailsIfAuthDoesNotExist()
    {
        $this->expectException(AuthorizationException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new Verified());
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );

        $response = $md->handle($request);
    }
    
    public function testFailsIfUserIsUnauthorized()
    {
        $this->expectException(AuthorizationException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new Verified());
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );

        $response = $md->handle($request);
    }
    
    public function testAttributesGetsPassedToException()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $md->add(new Verified(
            message: 'Custom',
            messageLevel: 'warning',
            redirectRoute: 'login',
            redirectUri: '/login',
        ));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $errorHappened = false;
        
        try {
            $response = $md->handle($request);
        } catch (AuthorizationException $e) {
            $errorHappened = true;
            $this->assertSame('Custom', $e->getMessage());
            $this->assertSame('warning', $e->messageLevel());
            $this->assertSame('/login', $e->redirectUri());
            $this->assertSame('login', $e->redirectRoute());
        }
        
        $this->assertTrue($errorHappened);
    }
}