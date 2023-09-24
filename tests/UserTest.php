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
use Tobento\App\User\User;
use Tobento\App\User\UserInterface;
use Tobento\Service\Acl\Authorizable;
    
class UserTest extends TestCase
{    
    public function testThatImplementsInterfaces()
    {
        $user = new User();
        
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertInstanceOf(Authorizable::class, $user);
    }
    
    public function testAuthenticatedMethods()
    {
        $user = new User();
        
        $this->assertFalse($user->isAuthenticated());
        
        $user->setAuthenticated(true);
        
        $this->assertTrue($user->isAuthenticated());
    }
    
    public function testGetVerifiedMethod()
    {
        $user = new User();
        
        $this->assertSame([], $user->getVerified());
        
        $user->setVerified(['email' => '2023-09-24 00:00:00']);
        
        $this->assertSame(['email' => '2023-09-24 00:00:00'], $user->getVerified());
    }
    
    public function testGetVerifiedAtMethod()
    {
        $user = new User();
        
        $user->setVerified(['email' => '2023-09-24 00:00:00']);
        
        $this->assertSame('2023-09-24 00:00:00', $user->getVerifiedAt('email'));
        
        $this->assertSame(null, $user->getVerifiedAt('smartphone'));
    }
    
    public function testIsVerifiedMethod()
    {
        $user = new User();
        
        $user->setVerified(['email' => '2023-09-24 00:00:00', 'smartphone' => '2024-09-24 00:00:00']);
        
        $this->assertFalse($user->isVerified([]));
        $this->assertTrue($user->isVerified(['email']));
        $this->assertTrue($user->isVerified(['email', 'smartphone']));
        $this->assertFalse($user->isVerified(['email', 'smartphone', 'slack']));
        $this->assertFalse($user->isVerified(['email', 'slack']));
    }
    
    public function testIsOneVerifiedMethod()
    {
        $user = new User();
        
        $this->assertFalse($user->isOneVerified());
        
        $user->setVerified(['email' => '2023-09-24 00:00:00', 'smartphone' => '2024-09-24 00:00:00']);
        
        $this->assertTrue($user->isOneVerified());
        $this->assertFalse($user->isOneVerified([]));
        $this->assertTrue($user->isOneVerified(['email']));
        $this->assertTrue($user->isOneVerified(['email', 'smartphone']));
        $this->assertTrue($user->isOneVerified(['email', 'smartphone', 'slack']));
        $this->assertTrue($user->isOneVerified(['email', 'slack']));
    }
}