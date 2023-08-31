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
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\Authentication\Token\TokenInterface;
use DateTimeImmutable;

class TokenTest extends TestCase
{
    public function testToken()
    {
        $issuedAt = new DateTimeImmutable('now');
        $expiresAt = new DateTimeImmutable('now');
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedBy: 'storage',
            issuedAt: $issuedAt,
            expiresAt: $expiresAt,
        );
        
        $this->assertInstanceOf(TokenInterface::class, $token);
        
        $this->assertSame('ID', $token->id());
        $this->assertSame(['userId' => 1], $token->payload());
        $this->assertSame('via', $token->authenticatedVia());
        $this->assertSame('by', $token->authenticatedBy());
        $this->assertSame('storage', $token->issuedBy());
        $this->assertSame($issuedAt, $token->issuedAt());
        $this->assertSame($expiresAt, $token->expiresAt());
    }
    
    public function testTokenWithNullValues()
    {
        $issuedAt = new DateTimeImmutable('now');
        
        $token = new Token(
            id: 'ID',
            payload: ['userId' => 1],
            authenticatedVia: 'via',
            authenticatedBy: null,
            issuedBy: 'storage',
            issuedAt: $issuedAt,
        );
        
        $this->assertSame('ID', $token->id());
        $this->assertSame(['userId' => 1], $token->payload());
        $this->assertSame('via', $token->authenticatedVia());
        $this->assertSame(null, $token->authenticatedBy());
        $this->assertSame('storage', $token->issuedBy());
        $this->assertSame($issuedAt, $token->issuedAt());
        $this->assertSame(null, $token->expiresAt());
    }
}