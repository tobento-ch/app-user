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
 
namespace Tobento\App\User\Boot;

use Tobento\App\Boot;
use Tobento\App\User\RoleRepositoryInterface;
use Tobento\App\Boot\Functions;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Acl\Acl as AclService;
use Tobento\Service\Acl\Role;
use Tobento\Service\Acl\Rule;

/**
 * Acl
 */
class Acl extends Boot
{
    public const INFO = [
        'boot' => [
            'implements acl interface and set roles',
        ],
    ];
    
    public const BOOT = [
        Functions::class,
    ];

    /**
     * Boot application services.
     *
     * @param Functions $functions
     * @return void
     */
    public function boot(Functions $functions): void
    {
        $this->app->set(AclInterface::class, function() {
            
            $acl = new AclService();
            
            if ($this->app->has(RoleRepositoryInterface::class)) {
                $acl->setRoles($this->app->get(RoleRepositoryInterface::class)->findAll()->all());
            }
            
            // at least guest role needs to be available:
            if (! $acl->hasRole('guest')) {
                $acl->setRoles([
                    new Role('guest'),
                ]);
            }
            
            return $acl;
        });
        
        $functions->register($this->app->dir('vendor').'tobento/service-acl/src/functions.php');
    }

    /**
     * Add a new rule.
     *
     * @param string $key
     * @return Rule
     */
    public function rule(string $key): Rule
    {
        return $this->app->get(AclInterface::class)->rule($key);
    }
}