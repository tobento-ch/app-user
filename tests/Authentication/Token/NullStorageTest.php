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
use Tobento\App\User\Authentication\Token\NullStorage;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\Token\Token;
use Tobento\App\User\Exception\TokenCreateException;
use Tobento\App\User\Exception\TokenDeleteException;
use Tobento\App\User\Exception\TokenNotFoundException;
use DateTimeImmutable;

class NullStorageTest extends TestCase
{
    public function testThatImplementsTokenStorageInterface()
    {
        $this->assertInstanceOf(TokenStorageInterface::class, new NullStorage());
    }
    
    public function testNameMethod()
    {
        $this->assertSame('null', (new NullStorage())->name());
    }
    
    public function testFetchTokenMethod()
    {
        $this->expectException(TokenNotFoundException::class);
        
        (new NullStorage())->fetchToken('ID');
    }
    
    public function testCreateTokenMethod()
    {
        $this->expectException(TokenCreateException::class);
        
        (new NullStorage())->createToken(
            payload: [],
            authenticatedVia: 'via',
        );
    }
    
    public function testDeleteTokenMethod()
    {
        $this->expectException(TokenDeleteException::class);
        
        $token = new Token(
            id: 'ID',
            payload: [],
            authenticatedVia: 'via',
            authenticatedBy: 'by',
            issuedBy: 'storageName',
            issuedAt: new DateTimeImmutable('now'),
        );
        
        (new NullStorage())->deleteToken($token);
    }
}