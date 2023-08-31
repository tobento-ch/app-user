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
use Tobento\App\User\Boot\Acl;
use Tobento\App\User\RoleRepositoryInterface;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Acl\Rule;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;

class AclTest extends TestCase
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
        $app->boot(Acl::class);
        $app->booting();
        
        $this->assertInstanceof(AclInterface::class, $app->get(AclInterface::class));
    }
    
    public function testGuestIsAvailable()
    {
        $app = $this->createApp();
        $app->boot(Acl::class);
        $app->booting();

        $this->assertTrue($app->get(AclInterface::class)->hasRole('guest'));
    }
    
    public function testRolesAreAddedFromRoleRepository()
    {
        $app = $this->createApp();
        
        $acl = $app->set(RoleRepositoryInterface::class, Factory::createRoleRepository(roles: [
            ['key' => 'editor'],
        ]));
        
        $app->boot(Acl::class);
        $app->booting();
        
        $acl = $app->get(AclInterface::class);
        
        $this->assertTrue($acl->hasRole('editor'));
        $this->assertTrue($acl->hasRole('guest'));
        $this->assertFalse($acl->hasRole('author'));
    }
    
    public function testAddingRulesFromAclBoot()
    {
        $app = $this->createApp();
        
        $acl = $app->set(RoleRepositoryInterface::class, Factory::createRoleRepository(roles: [
            ['key' => 'editor'],
        ]));
        
        $app->boot(Acl::class);
        $app->booting();
        
        $this->assertFalse(isset($app->get(AclInterface::class)->getRules()['articles.read']));
        
        $rule = $app->get(Acl::class)->rule(key: 'articles.read');
        
        $this->assertInstanceof(Rule::class, $rule);
        
        $this->assertTrue(isset($app->get(AclInterface::class)->getRules()['articles.read']));
    }
}