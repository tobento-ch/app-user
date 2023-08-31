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

namespace Tobento\App\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\User\UserInterface;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Authenticated;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\Token\InMemoryStorage;
use Tobento\App\User\Exception\TokenExpiredException;
use Tobento\App\Http\Boot\Http;
use Tobento\App\Http\Boot\Middleware;
use Tobento\App\Http\ResponseEmitterInterface;
use Tobento\App\Http\Test\TestResponse;
use Tobento\App\Http\Test\Mock\ResponseEmitter;
use Tobento\App\Http\Test\Mock\SessionMiddleware;
use Tobento\App\Http\Test\Mock\MiddlewareBoot;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Filesystem\Dir;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

/**
 * Some Basic App tests: deeper tested on indvidual tests!
 */
class AppTest extends TestCase
{
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/app/');
        }
        
        (new Dir())->create(__DIR__.'/app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../'), 'root')
            ->dir(realpath(__DIR__.'/app/'), 'app')
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
    
    public function testRetrieveCurrentUserWhenUnauthenticated()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Middleware::class);
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\User::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'foo',
                serverParams: [],
            );
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            $user = $request->getAttribute(UserInterface::class);
            return ['authenticated' => $user->isAuthenticated(), 'role' => $user->getRoleKey()];
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(200)
            ->isBodySame('{"authenticated":false,"role":"guest"}');
    }
    
    public function testRetrieveAuthenticatedUserWhenUnauthenticated()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Middleware::class);
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\User::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'foo',
                serverParams: [],
            );
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            $auth = $request->getAttribute(AuthInterface::class);
            
            return ['authenticated' => $auth->hasAuthenticated()];
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(200)
            ->isBodySame('{"authenticated":false}');
    }
    
    public function testRetrieveAuthenticatedUserWhenAuthenticated()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Middleware::class);
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\User::class);
        
        // create token:
        $tokenStorage = new InMemoryStorage(clock: new \Tobento\Service\Clock\SystemClock());
        $token = $tokenStorage->createToken(
            payload: ['userId' => 5],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedAt: new \DateTimeImmutable('now'),
            expiresAt: new \DateTimeImmutable('now +10 minutes'),
        );
        
        // Change token storage:        
        $app->on(TokenStorageInterface::class, function() use ($tokenStorage) {
            return $tokenStorage;
        });
        
        // Create user:
        $app->on(UserRepositoryInterface::class, function($repo) {
            $repo->create(['id' => 5, 'username' => 'tom']);
        });
        
        $app->on(ServerRequestInterface::class, function() use ($token) {            
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'foo',
                serverParams: [],
            )->withHeader('X-Auth-Token', $token->id());
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            $auth = $request->getAttribute(AuthInterface::class);
            
            return ['authenticated' => $auth->hasAuthenticated()];
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(200)
            ->isBodySame('{"authenticated":true}');
    }
    
    public function testAuthenticateUserWithSessionStorage()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Middleware::class);
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\Http\Boot\Session::class);
        $app->boot(\Tobento\App\User\Boot\User::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(
                method: 'POST',
                uri: 'login',
                serverParams: [],
            );
        });
        
        // Replaces session middleware to ignore session start and save exceptions.
        $app->on(Middleware::class, MiddlewareBoot::class);

        // Change token storage:
        $app->on(TokenStorageInterface::class, function() use ($app) {
            return new \Tobento\App\User\Authentication\Token\SessionStorage(
                session: $app->get(SessionInterface::class),
                clock: new \Tobento\Service\Clock\SystemClock(),
                regenerateId: false,
            );
        });
        
        // Create user:
        $app->on(UserRepositoryInterface::class, function($repo) {
            $repo->create(['id' => 5, 'username' => 'tom']);
        });
        
        $app->booting();
        
        $app->route('POST', 'login', function(
            ServerRequestInterface $request,
            UserRepositoryInterface $userRepository,
            AuthInterface $auth,
            TokenStorageInterface $tokenStorage,
        ) {
            // authenticate user manually:
            $user = $userRepository->findById(5);

            if (is_null($user)) {
                return 'UserNotFound';
            }

            // create token and start auth:
            $token = $tokenStorage->createToken(
                // Set the payload:
                payload: ['userId' => $user->id()],
                authenticatedVia: 'login.auth',
                authenticatedBy: null,
                issuedAt: new \DateTimeImmutable('now'),
                expiresAt: new \DateTimeImmutable('now +10 minutes'),
            );

            $auth->start(new Authenticated(token: $token, user: $user));

            // create and return response:
            return 'Authenticated';
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(200)
            ->isBodySame('Authenticated');
    }
    
    public function testVerifyPermissionByMiddlewarePermissionDenied()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Middleware::class);
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        $app->boot(\Tobento\App\User\Boot\User::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'articles');
        });

        // Give permission for user:
        $app->on(AclInterface::class, function($acl) {
            $acl->rule('articles');
        });
        
        $app->booting();
        
        $app->route('GET', 'articles', function() {
            return 'HasPermission';
        })->middleware(\Tobento\App\User\Middleware\VerifyPermission::class)->name('articles');

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(403)
            ->isBodySame('You don\'t have a required "articles" permission.');
    }
    
    public function testVerifyPermissionByMiddlewarePermissionGranted()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Middleware::class);
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        $app->boot(\Tobento\App\User\Boot\User::class);
        
        $app->on(ServerRequestInterface::class, function() {
            return (new Psr17Factory())->createServerRequest(method: 'GET', uri: 'articles');
        });
        
        // Give permission for user:
        $app->on(AclInterface::class, function($acl) {
            $acl->rule('articles');
            $acl->setPermissions(['articles']);
        });
        
        $app->booting();
        
        $app->route('GET', 'articles', function() {
            return 'HasPermission';
        })->middleware(\Tobento\App\User\Middleware\VerifyPermission::class)->name('articles');

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(200);
    }    
    
    public function testTokenExpiredWithUserErrorHandler()
    {
        $app = $this->createApp();
        $app->boot(\Tobento\App\Http\Boot\Middleware::class);
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\User\Boot\HttpUserErrorHandler::class);
        $app->boot(\Tobento\App\User\Boot\User::class);
        
        // create token:
        $tokenStorage = new InMemoryStorage(clock: new \Tobento\Service\Clock\SystemClock());
        $token = $tokenStorage->createToken(
            payload: ['userId' => 5],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedAt: new \DateTimeImmutable('now -20 minutes'),
            expiresAt: new \DateTimeImmutable('now -10 minutes'),
        );
        
        // Change token storage:        
        $app->on(TokenStorageInterface::class, function() use ($tokenStorage) {
            return $tokenStorage;
        });
        
        // Create user:
        $app->on(UserRepositoryInterface::class, function($repo) {
            $repo->create(['id' => 5, 'username' => 'tom']);
        });
        
        $app->on(ServerRequestInterface::class, function() use ($token) {            
            return (new Psr17Factory())->createServerRequest(
                method: 'GET',
                uri: 'foo',
                serverParams: [],
            )->withHeader('X-Auth-Token', $token->id());
        });
        
        $app->booting();
        
        $app->route('GET', 'foo', function(ServerRequestInterface $request) {
            $auth = $request->getAttribute(AuthInterface::class);
            
            return ['authenticated' => $auth->hasAuthenticated()];
        });

        $app->run();

        (new TestResponse($app->get(Http::class)->getResponse()))
            ->isStatusCode(403)
            ->isBodySame('419 | Resource Expired');
    }
}