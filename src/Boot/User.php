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
use Tobento\App\Boot\Config;
use Tobento\App\Migration\Boot\Migration;
use Tobento\App\Http\Boot\Middleware;
use Tobento\App\Database\Boot\Database;
use Tobento\App\Validation\Boot\HttpValidationErrorHandler;
use Tobento\App\Validation\Boot\Validator;
use Tobento\App\Country\Boot\Country;
use Tobento\App\User\Authentication\Token\TokenTransportsInterface;
use Tobento\Service\Cookie\CookiesProcessorInterface;

/**
 * User
 */
class User extends Boot
{
    public const INFO = [
        'boot' => [
            'installs and loads user config file',
            'user and role repositories implementation based on config',
            'adds middleware for authentication and authorization based on config',
        ],
    ];

    public const BOOT = [
        Config::class,
        Migration::class,
        Middleware::class,
        Acl::class,
        Database::class,
        HttpValidationErrorHandler::class,
        Validator::class,
        Country::class,
    ];

    /**
     * Boot application services.
     *
     * @param Config $config
     * @param Migration $migration
     * @param Middleware $middleware
     * @return void
     */
    public function boot(
        Config $config,
        Migration $migration,
        Middleware $middleware,
    ): void {
        // install user migrations:
        $migration->install(\Tobento\App\User\Migration\User::class);
        
        // load the user config:
        $config = $config->load('user.php');

        // adding middlewares:
        foreach($config['middlewares'] as $mw) {
            $middleware->add($mw, priority: 5900);
        }
        
        $middleware->addAliases($config['middleware_aliases']);
        
        // setting interfaces:
        foreach($config['interfaces'] as $interface => $implementation) {
            $this->app->set($interface, $implementation);
        }
        
        // install migration after interfaces are set:
        foreach($config['migrations'] as $migrationClass) {
            $migration->install($migrationClass);
        }
        
        // whitelist token cookie:
        $this->app->on(CookiesProcessorInterface::class, function(CookiesProcessorInterface $processor) {
            $transports = $this->app->get(TokenTransportsInterface::class);

            if ($transports->has('cookie')) {
                $processor->whitelistCookie(name: $transports->get('cookie')->cookieName());
            }
        });
    }
}