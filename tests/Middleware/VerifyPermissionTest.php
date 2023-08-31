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
use Tobento\App\User\Middleware\VerifyPermission;
use Tobento\App\User\User;
use Tobento\App\User\Exception\PermissionDeniedException;
use Tobento\Service\Acl\Acl;
use Tobento\Service\Acl\Role;
use Tobento\Service\Middleware\MiddlewareDispatcherInterface;
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Container\Container;
use Nyholm\Psr7\Factory\Psr17Factory;

class VerifyPermissionTest extends TestCase
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

    public function testHasPermissionUsingRouteName()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article.read']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article.read');
        
        $md->add(new VerifyPermission(acl: $acl));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $request = $request->withAttribute('route.name', 'article.read');

        $response = $md->handle($request);
        
        $this->assertTrue(true);
    }
    
    public function testHasPermissionUsingRouteCanParameter()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article.read']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article.read');
        
        $md->add(new VerifyPermission(acl: $acl));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );
        
        $request = $request->withAttribute('route.can', 'article.read');

        $response = $md->handle($request);
        
        $this->assertTrue(true);
    }
    
    public function testHasPermissionUsingMiddlewarePermission()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article.read']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article.read');
        
        $md->add(new VerifyPermission(
            acl: $acl,
            permission: 'article.read',
        ));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );

        $response = $md->handle($request);
        
        $this->assertTrue(true);
    }
    
    public function testFailsIfNoPermission()
    {
        $this->expectException(PermissionDeniedException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article.read']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article.read');
        
        $md->add(new VerifyPermission(
            acl: $acl,
            permission: 'article.write',
        ));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );

        $response = $md->handle($request);
    }
    
    public function testFailsIfNoPermissionDefined()
    {
        $this->expectException(PermissionDeniedException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article.read']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article.read');
        
        $md->add(new VerifyPermission(
            acl: $acl,
        ));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );

        $response = $md->handle($request);
    }
    
    public function testAttributesGetsPassedToException()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        $user->addPermissions(['article.read']);
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        $acl->rule('article.read');
        
        $md->add(new VerifyPermission(
            acl: $acl,
            permission: 'article.write',
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