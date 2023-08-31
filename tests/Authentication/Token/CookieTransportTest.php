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

namespace Tobento\App\Test\Authentication\Token;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Authentication\Token\CookieTransport;
use Tobento\App\User\Authentication\Token\TokenTransportInterface;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\Service\Cookie\CookieValuesInterface;
use Tobento\Service\Cookie\CookieValues;
use Tobento\Service\Cookie\CookiesInterface;
use Tobento\Service\Cookie\Cookies;
use Tobento\Service\Cookie\CookieFactory;
use Tobento\Service\Clock\FrozenClock;
use Nyholm\Psr7\Factory\Psr17Factory;
use DateTimeImmutable;

class CookieTransportTest extends TestCase
{
    public function testThatImplementsTokenTransportInterface()
    {
        $this->assertInstanceOf(
            TokenTransportInterface::class,
            new CookieTransport(clock: new FrozenClock())
        );
    }
    
    public function testNameMethod()
    {
        $this->assertSame('cookie', (new CookieTransport(clock: new FrozenClock()))->name());
    }
    
    public function testFetchTokenIdMethodReturnsNull()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $tokenId = (new CookieTransport(clock: new FrozenClock()))->fetchTokenId(request: $request);
        
        $this->assertNull($tokenId);
    }
    
    public function testFetchTokenIdMethodReturnsToken()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $request = $request->withAttribute(
            CookieValuesInterface::class,
            new CookieValues(['token' => 'ID'])
        );
        
        $tokenId = (new CookieTransport(clock: new FrozenClock()))->fetchTokenId(request: $request);
        
        $this->assertSame('ID', $tokenId);
    }
    
    public function testCommitAndRemoveTokenMethod()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $cookies = new Cookies(
            cookieFactory: new CookieFactory(),
        );
        
        $request = $request->withAttribute(CookiesInterface::class, $cookies);

        $token = new Token(
            id: 'ID',
            payload: [],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedBy: 'storageName',
            issuedAt: new DateTimeImmutable('now'),
            expiresAt: new DateTimeImmutable('now +5 minutes'),
        );
        
        $transport = new CookieTransport(clock: new FrozenClock());
        
        $response = $transport->commitToken(
            token: $token,
            request: $request,
            response: (new Psr17Factory())->createResponse(200)
        );
        
        $this->assertSame('token', $cookies->get($transport->cookieName())?->name());
        $this->assertSame('ID', $cookies->get($transport->cookieName())?->value());
        $this->assertSame(300, $cookies->get($transport->cookieName())?->lifetime());
        
        $response = $transport->removeToken(
            token: $token,
            request: $request,
            response: (new Psr17Factory())->createResponse(200)
        );
        
        $this->assertSame(-86400, $cookies->get($transport->cookieName())?->lifetime());
    }
    
    public function testCommitMethodLifetimeIfExpired()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $cookies = new Cookies(
            cookieFactory: new CookieFactory(),
        );
        
        $request = $request->withAttribute(CookiesInterface::class, $cookies);

        $token = new Token(
            id: 'ID',
            payload: [],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedBy: 'storageName',
            issuedAt: new DateTimeImmutable('now'),
            expiresAt: new DateTimeImmutable('now +5 minutes'),
        );
        
        $transport = new CookieTransport(clock: (new FrozenClock())->modify('+6 minutes'));
        
        $response = $transport->commitToken(
            token: $token,
            request: $request,
            response: (new Psr17Factory())->createResponse(200)
        );
        
        $this->assertSame(0, $cookies->get($transport->cookieName())?->lifetime());
    }    
}