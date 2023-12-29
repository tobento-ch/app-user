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

namespace Tobento\App\Test\Authentication;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Authentication\Auth;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Authenticated;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\User;
use DateTimeImmutable;

class AuthTest extends TestCase
{    
    public function testThatImplementsAuthInterface()
    {
        $this->assertInstanceOf(AuthInterface::class, new Auth());
    }
    
    public function testAuthenticationWorkflow()
    {
        $auth = new Auth();
        
        $authenticated = new Authenticated(
            token: new Token(
                id: 'id',
                payload: [],
                authenticatedVia: 'custom',
                authenticatedBy: null,
                issuedBy: 'storageName',
                issuedAt: new DateTimeImmutable('now'),
            ),
            user: new User(id: 1),
        );
        
        $this->assertFalse($auth->hasAuthenticated());
        $this->assertNull($auth->getAuthenticated());
        $this->assertNull($auth->getUnauthenticated());
        $this->assertFalse($auth->isClosed());
        
        $auth->start(
            authenticated: $authenticated,
        );
        
        $this->assertNull($auth->getTokenTransportName());
        $this->assertTrue($auth->hasAuthenticated());
        $this->assertTrue($authenticated === $auth->getAuthenticated());
        $this->assertTrue($auth->getAuthenticated()->user()->isAuthenticated());
        $this->assertNull($auth->getUnauthenticated());
        $this->assertFalse($auth->isClosed());
        
        $auth->close();
        
        $this->assertNotNull($auth->getUnauthenticated());
        $this->assertFalse($auth->hasAuthenticated());
        $this->assertNull($auth->getAuthenticated());
        $this->assertFalse($authenticated->user()->isAuthenticated());
        $this->assertTrue($auth->isClosed());
    }
    
    public function testClosingWithoutStartingBefore()
    {
        $auth = new Auth();
        
        $authenticated = new Authenticated(
            token: new Token(
                id: 'id',
                payload: [],
                authenticatedVia: 'custom',
                authenticatedBy: null,
                issuedBy: 'storageName',
                issuedAt: new DateTimeImmutable('now'),
            ),
            user: new User(id: 1),
        );
        
        $auth->close();
        
        $this->assertFalse($auth->hasAuthenticated());
        $this->assertNull($auth->getAuthenticated());
        $this->assertFalse($authenticated->user()->isAuthenticated());
        $this->assertTrue($auth->isClosed());
    }
}