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
use Tobento\App\User\Authentication\Authenticated;
use Tobento\App\User\Authentication\AuthenticatedInterface;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\User;
use DateTimeImmutable;

class AuthenticatedTest extends TestCase
{
    public function testAuthenticatedMethods()
    {
        $token = new Token(
            id: 'id',
            payload: [],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedBy: 'storageName',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        $user = new User(id: 1);
        
        $authenticated = new Authenticated(
            token: $token,
            user: $user,
        );
        
        $this->assertInstanceOf(AuthenticatedInterface::class, $authenticated);
        
        $this->assertTrue($token === $authenticated->token());
        $this->assertTrue($user === $authenticated->user());
        $this->assertSame('via', $authenticated->via());
        $this->assertSame('by', $authenticated->by());
    }
}