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
use Tobento\App\User\Authentication\Token\TokenStorages;
use Tobento\App\User\Authentication\Token\TokenStoragesInterface;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\Token\NullStorage;

class TokenStoragesTest extends TestCase
{
    public function testConstructMethod()
    {
        $this->assertInstanceof(TokenStoragesInterface::class, new TokenStorages());

        $nullStorage = new NullStorage();        
        $storages = new TokenStorages($nullStorage);
        
        $this->assertTrue($storages->has('null'));
        $this->assertSame($nullStorage, $storages->get('null'));
    }
    
    public function testAddGetHasMethods()
    {
        $nullStorage = new NullStorage();
        $storages = new TokenStorages();
        
        $this->assertFalse($storages->has('null'));
        
        $storages->add($nullStorage);
        
        $this->assertTrue($storages->has('null'));
        $this->assertSame($nullStorage, $storages->get('null'));
    }
    
    public function testRegisterGetHasMethods()
    {
        $storages = new TokenStorages();
        
        $this->assertFalse($storages->has('null'));
        
        $storages->register('null', function () {
            return new NullStorage();
        });
        
        $this->assertTrue($storages->has('null'));
        $this->assertInstanceof(TokenStorageInterface::class, $storages->get('null'));
    }
    
    public function testNamesMethod()
    {
        $storages = new TokenStorages();

        $storages->register('null', function () {
            return new NullStorage();
        });
        
        $this->assertSame(['null'], $storages->names());
    }
}