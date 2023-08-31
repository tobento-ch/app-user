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
use Tobento\App\User\Middleware\VerifyRole;
use Tobento\App\User\User;
use Tobento\App\User\Exception\RoleDeniedException;
use Tobento\Service\Acl\Acl;
use Tobento\Service\Acl\Role;
use Tobento\Service\Middleware\MiddlewareDispatcherInterface;
use Tobento\Service\Middleware\MiddlewareDispatcher;
use Tobento\Service\Middleware\AutowiringMiddlewareFactory;
use Tobento\Service\Middleware\FallbackHandler;
use Tobento\Service\Container\Container;
use Nyholm\Psr7\Factory\Psr17Factory;

class VerifyRoleTest extends TestCase
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

    public function testHasRole()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        
        $md->add(new VerifyRole(acl: $acl, role: 'editor'));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );

        $response = $md->handle($request);
        
        $this->assertTrue(true);
    }
    
    public function testHasOneOfRole()
    {
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('editor'));
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        
        $md->add(new VerifyRole(acl: $acl, role: 'administrator|editor'));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );

        $response = $md->handle($request);
        
        $this->assertTrue(true);
    }

    public function testFailsIfNoCurrentUser()
    {
        $this->expectException(RoleDeniedException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $acl = new Acl();
        
        $md->add(new VerifyRole(acl: $acl, role: 'administrator|editor'));
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
        );

        $response = $md->handle($request);
    }
    
    public function testFailsIfInvalidRole()
    {
        $this->expectException(RoleDeniedException::class);
        
        $md = $this->createMiddlewareDispatcher();
        
        $user = new User();
        $user->setRole(new Role('guest'));
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        
        $md->add(new VerifyRole(acl: $acl, role: 'administrator|editor'));
        
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
        $user->setRole(new Role('guest'));
        
        $acl = new Acl();
        $acl->setCurrentUser($user);
        
        $md->add(new VerifyRole(
            acl: $acl,
            role: 'editor',
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
        } catch (RoleDeniedException $e) {
            $errorHappened = true;
            $this->assertSame('Custom', $e->getMessage());
            $this->assertSame('warning', $e->messageLevel());
            $this->assertSame('/login', $e->redirectUri());
            $this->assertSame('login', $e->redirectRoute());
        }
        
        $this->assertTrue($errorHappened);
    }
}