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

namespace Tobento\App\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Test\Factory;
use Tobento\App\User\Boot\User;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\UserFactoryInterface;
use Tobento\App\User\AddressRepositoryInterface;
use Tobento\App\User\AddressFactoryInterface;
use Tobento\App\User\RoleRepositoryInterface;
use Tobento\App\User\RoleFactoryInterface;
use Tobento\App\User\Authentication\AuthInterface;
use Tobento\App\User\Authentication\Token\TokenStoragesInterface;
use Tobento\App\User\Authentication\Token\TokenStorageInterface;
use Tobento\App\User\Authentication\Token\TokenTransportsInterface;
use Tobento\App\User\Authentication\Token\TokenTransportInterface;
use Tobento\App\User\Authenticator\TokenAuthenticatorInterface;
use Tobento\App\User\Authenticator\UserVerifierInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;

class UserTest extends TestCase
{    
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config')
            ->dir($app->dir('root').'vendor', 'vendor')
            // for testing only we add public within app dir.
            ->dir($app->dir('app').'public', 'public');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(User::class);
        $app->booting();
        
        $this->assertInstanceof(UserRepositoryInterface::class, $app->get(UserRepositoryInterface::class));
        $this->assertInstanceof(UserFactoryInterface::class, $app->get(UserFactoryInterface::class));
        $this->assertInstanceof(AddressRepositoryInterface::class, $app->get(AddressRepositoryInterface::class));
        $this->assertInstanceof(AddressFactoryInterface::class, $app->get(AddressFactoryInterface::class));
        $this->assertInstanceof(RoleRepositoryInterface::class, $app->get(RoleRepositoryInterface::class));
        $this->assertInstanceof(RoleFactoryInterface::class, $app->get(RoleFactoryInterface::class));
        
        $this->assertInstanceof(AuthInterface::class, $app->get(AuthInterface::class));
        $this->assertInstanceof(TokenStoragesInterface::class, $app->get(TokenStoragesInterface::class));
        $this->assertInstanceof(TokenStorageInterface::class, $app->get(TokenStorageInterface::class));
        $this->assertInstanceof(TokenTransportsInterface::class, $app->get(TokenTransportsInterface::class));
        $this->assertInstanceof(TokenTransportInterface::class, $app->get(TokenTransportInterface::class));
        $this->assertInstanceof(TokenAuthenticatorInterface::class, $app->get(TokenAuthenticatorInterface::class));
        $this->assertInstanceof(UserVerifierInterface::class, $app->get(UserVerifierInterface::class));
    }
}