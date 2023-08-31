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

namespace Tobento\App\User\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\PasswordHasher;
use Tobento\App\User\PasswordHasherInterface;
    
class PasswordHasherTest extends TestCase
{    
    public function testThatImplementsPasswordHasherInterface()
    {
        $this->assertInstanceOf(
            PasswordHasherInterface::class,
            new PasswordHasher()
        );
    }
    
    public function testHashAndVerify()
    {
        $hasher = new PasswordHasher();
        $hashed = $hasher->hash(plainPassword: '123456');
        
        $this->assertNotSame('123456', $hashed);
        $this->assertTrue($hasher->verify(hashedPassword: $hashed, plainPassword: '123456'));
        $this->assertFalse($hasher->verify(hashedPassword: $hashed, plainPassword: '3456'));
        $this->assertFalse($hasher->verify(hashedPassword: '123456', plainPassword: '123456'));
    }
}