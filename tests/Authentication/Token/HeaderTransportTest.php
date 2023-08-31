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
use Tobento\App\User\Authentication\Token\HeaderTransport;
use Tobento\App\User\Authentication\Token\TokenTransportInterface;
use Tobento\App\User\Authentication\Token\Token;
use Nyholm\Psr7\Factory\Psr17Factory;
use DateTimeImmutable;

class HeaderTransportTest extends TestCase
{
    public function testThatImplementsTokenTransportInterface()
    {
        $this->assertInstanceOf(TokenTransportInterface::class, new HeaderTransport());
    }
    
    public function testNameMethod()
    {
        $this->assertSame('header', (new HeaderTransport())->name());
        $this->assertSame('foo', (new HeaderTransport(name: 'foo'))->name());
    }
    
    public function testFetchTokenIdMethodReturnsNull()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $tokenId = (new HeaderTransport())->fetchTokenId(request: $request);
        
        $this->assertNull($tokenId);
    }
    
    public function testFetchTokenIdMethodReturnsToken()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $request = $request->withHeader('X-Auth-Token', 'ID');
        
        $tokenId = (new HeaderTransport())->fetchTokenId(request: $request);
        
        $this->assertSame('ID', $tokenId);
    }
    
    public function testFetchTokenIdMethodReturnsTokenFromCustomHeader()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $request = $request->withHeader('X-Token', 'ID');
        
        $tokenId = (new HeaderTransport(headerName: 'X-Token'))->fetchTokenId(request: $request);
        
        $this->assertSame('ID', $tokenId);
    }    
    
    public function testCommitAndRemoveTokenMethod()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $token = new Token(
            id: 'ID',
            payload: [],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedBy: 'storageName',
            issuedAt: new DateTimeImmutable('now'),
            expiresAt: new DateTimeImmutable('now +5 minutes'),
        );
        
        $transport = new HeaderTransport();
        
        $response = $transport->commitToken(
            token: $token,
            request: $request,
            response: (new Psr17Factory())->createResponse(200)
        );
        
        $this->assertSame(['ID'], $response->getHeader('X-Auth-Token'));
        
        $response = $transport->removeToken(
            token: $token,
            request: $request,
            response: (new Psr17Factory())->createResponse(200)
        );
        
        $this->assertFalse($response->hasHeader('X-Auth-Token'));
    }
}