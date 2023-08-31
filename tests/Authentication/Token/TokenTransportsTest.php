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
use Tobento\App\User\Authentication\Token\TokenTransports;
use Tobento\App\User\Authentication\Token\TokenTransportsInterface;
use Tobento\App\User\Authentication\Token\TokenTransportInterface;
use Tobento\App\User\Authentication\Token\HeaderTransport;
use Tobento\App\User\Authentication\Token\Token;
use Nyholm\Psr7\Factory\Psr17Factory;
use DateTimeImmutable;

class TokenTransportsTest extends TestCase
{
    public function testConstructMethod()
    {
        $this->assertInstanceof(TokenTransportsInterface::class, new TokenTransports());
        $this->assertInstanceof(TokenTransportInterface::class, new TokenTransports());

        $transport = new HeaderTransport();
        $transports = new TokenTransports($transport);
        
        $this->assertTrue($transports->has('header'));
        $this->assertSame($transport, $transports->get('header'));
    }
    
    public function testAddGetHasMethods()
    {
        $transport = new HeaderTransport();
        $transports = new TokenTransports();
        
        $this->assertFalse($transports->has('header'));
        
        $transports->add($transport);
        
        $this->assertTrue($transports->has('header'));
        $this->assertSame($transport, $transports->get('header'));
    }
    
    public function testRegisterGetHasMethods()
    {
        $transports = new TokenTransports();
        
        $this->assertFalse($transports->has('header'));
        
        $transports->register('header', function () {
            return new HeaderTransport();
        });
        
        $this->assertTrue($transports->has('header'));
        $this->assertInstanceof(TokenTransportInterface::class, $transports->get('header'));
    }
    
    public function testOnlyMethod()
    {
        $transports = new TokenTransports(
            new HeaderTransport(),
            new HeaderTransport(name: 'foo'),
        );
        
        $this->assertTrue($transports->has('header'));
        $this->assertTrue($transports->has('foo'));
        
        $transportsNew = $transports->only(['foo']);
        
        $this->assertNotSame($transports, $transportsNew);
        $this->assertFalse($transportsNew->has('header'));
        $this->assertTrue($transportsNew->has('foo'));
    }
    
    public function testNameMethod()
    {
        $transports = new TokenTransports(
            new HeaderTransport(),
            new HeaderTransport(name: 'foo'),
        );
        
        $this->assertSame('transports', $transports->name());
    }
    
    public function testFetchTokenIdMethodReturnsNull()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $transports = new TokenTransports(
            new HeaderTransport(),
            new HeaderTransport(name: 'foo'),
        );
        
        $tokenId = $transports->fetchTokenId(request: $request);
        
        $this->assertNull($tokenId);
    }
    
    public function testFetchTokenIdMethodReturnsToken()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $transports = new TokenTransports(
            new HeaderTransport(),
            new HeaderTransport(name: 'foo'),
        );
        
        $request = $request->withHeader('X-Auth-Token', 'ID');
        
        $tokenId = $transports->fetchTokenId(request: $request);
        
        $this->assertSame('ID', $tokenId);
    }
    
    public function testCommitAndRemoveTokenMethod()
    {
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo',
            serverParams: [],
        );
        
        $request = $request->withHeader('X-Auth-Token', 'ID');
        
        $transports = new TokenTransports(
            new HeaderTransport(),
            new HeaderTransport(name: 'foo'),
        );
        
        $tokenId = $transports->fetchTokenId(request: $request);
        
        $token = new Token(
            id: 'ID',
            payload: [],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedBy: 'storageName',
            issuedAt: new DateTimeImmutable('now'),
            expiresAt: new DateTimeImmutable('now +5 minutes'),
        );
        
        $response = $transports->commitToken(
            token: $token,
            request: $request->withoutHeader('X-Auth-Token'),
            response: (new Psr17Factory())->createResponse(200)
        );
        
        $this->assertSame(['ID'], $response->getHeader('X-Auth-Token'));
        
        $response = $transports->removeToken(
            token: $token,
            request: $request,
            response: (new Psr17Factory())->createResponse(200)
        );
        
        $this->assertFalse($response->hasHeader('X-Auth-Token'));
    }
}