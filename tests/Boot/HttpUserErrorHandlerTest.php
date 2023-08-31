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

namespace Tobento\App\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Test\Factory;
use Tobento\App\User\Boot\HttpUserErrorHandler;
use Tobento\App\User\Exception\TokenExpiredException;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\App\User\Exception\AuthorizationException;
use Tobento\App\Http\Boot\Http;
use Tobento\App\Http\ResponseEmitterInterface;
use Tobento\App\Http\Test\TestResponse;
use Tobento\App\Http\Test\Mock\ResponseEmitter;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class HttpUserErrorHandlerTest extends TestCase
{    
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config')
            ->dir($app->dir('root').'vendor', 'vendor')
            // for testing only we add public within app dir.
            ->dir($app->dir('app').'public', 'public');
        
        // Replace response emitter for testing:
        $app->on(ResponseEmitterInterface::class, ResponseEmitter::class);
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testTokenExpiredException()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            throw new TokenExpiredException();
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(403)
            ->isBodySame('419 | Resource Expired');
    }
    
    public function testTokenExpiredExceptionWithJson()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo')
                ->withAddedHeader('Accept', 'application/json, text/html');
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            throw new TokenExpiredException();
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(403)
            ->isBodySame('{"status":403,"message":"419 | Resource Expired"}');
    }
    
    public function testAuthenticationException()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            throw new AuthenticationException();
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(403)
            ->isBodySame('403 | Forbidden');
    }
    
    public function testAuthenticationExceptionWithJson()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo')
                ->withAddedHeader('Accept', 'application/json, text/html');
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            throw new AuthenticationException();
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(403)
            ->isBodySame('{"status":403,"message":"403 | Forbidden"}');
    }
    
    public function testAuthorizationException()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            throw new AuthorizationException();
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(403)
            ->isBodySame('Unauthorized.');
    }
    
    public function testAuthorizationExceptionWithJson()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo')
                ->withAddedHeader('Accept', 'application/json, text/html');
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            throw new AuthorizationException();
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(403)
            ->isBodySame('{"status":403,"message":"Unauthorized."}');
    }
    
    public function testAuthorizationExceptionWithRedirectUri()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            throw new AuthorizationException(
                redirectUri: 'login',
            );
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(302)
            ->hasHeader('location', 'login');
    }
    
    public function testAuthorizationExceptionWithRedirectRoute()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'foo');
        });
        
        $app->booting();

        $app->route('GET', 'login', function(ServerRequestInterface $request) {
            return 'login';
        })->name('login');
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            throw new AuthorizationException(
                redirectRoute: 'login',
            );
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(302)
            ->hasHeader('location', '/login');
    }
}