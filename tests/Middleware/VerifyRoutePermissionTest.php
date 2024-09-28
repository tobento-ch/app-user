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

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Tobento\App\User\Exception\PermissionDeniedException;
use Tobento\App\User\Middleware\VerifyRoutePermission;
use Tobento\App\User\User;
use Tobento\Service\Acl\Acl;
use Tobento\Service\Acl\Role;
use Tobento\Service\Container\Container;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Middleware\MiddlewareDispatcherInterface;
use Tobento\Service\Middleware\MiddlewareDispatcher;

class VerifyRoutePermissionTest extends TestCase
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

    public function testHasPermission()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article');
        
        $md->add(new VerifyRoutePermission(acl: $acl, permissions: ['article.show' => 'article']));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $request = $request->withAttribute('route.name', 'article.show');
        $response = $md->handle($request);
        
        $this->assertTrue(true);
    }
    
    public function testFailsIfNoRouteNameFound()
    {
        $this->expectException(PermissionDeniedException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article');
        
        $md->add(new VerifyRoutePermission(
            acl: $acl,
            permissions: ['article.show' => 'article'],
        ));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $response = $md->handle($request);
    }

    public function testFailsIfNoPermissionForTheRouteIsDefined()
    {
        $this->expectException(PermissionDeniedException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article');
        
        $md->add(new VerifyRoutePermission(
            acl: $acl,
            permissions: ['article.write' => 'article'],
        ));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $request = $request->withAttribute('route.name', 'article.show');
        $response = $md->handle($request);
    }
    
    public function testFailsIfUserHasNoPermission()
    {
        $this->expectException(PermissionDeniedException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article');
        
        $md->add(new VerifyRoutePermission(
            acl: $acl,
            permissions: ['article.show' => 'article'],
        ));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $request = $request->withAttribute('route.name', 'article.show');
        $response = $md->handle($request);
    }
    
    public function testAttributesGetsPassedToException()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article');
        
        $md->add(new VerifyRoutePermission(
            acl: $acl,
            permissions: ['article.show' => 'article'],
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
        } catch (PermissionDeniedException $e) {
            $errorHappened = true;
            $this->assertSame('Custom', $e->getMessage());
            $this->assertSame('warning', $e->messageLevel());
            $this->assertSame('/login', $e->redirectUri());
            $this->assertSame('login', $e->redirectRoute());
        }
        
        $this->assertTrue($errorHappened);
    }
}