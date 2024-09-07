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

namespace Tobento\App\User\Test\Console;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Console\AclRulesCommand;
use Tobento\Service\Acl\Acl;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Console\Test\TestCommand;
use Tobento\Service\Container\Container;
    
class AclRulesCommandTest extends TestCase
{    
    public function testCommand()
    {
        $acl = new Acl();
        $acl->rule('article.read')
            ->title('Article Read')
            ->description('If a user can read articles');
        
        $acl->rule('article.write')
            ->title('Article Write')
            ->description('If a user can write articles');
        
        $container = new Container();
        $container->set(AclInterface::class, $acl);
        
        $rows = [];
        
        foreach($acl->getRules() as $rule) {
            $rows[] = [
                $rule->getKey(),
                $rule->getTitle(),
                $rule->getDescription(),
                $rule->getArea(),
            ];
        }
        
        (new TestCommand(command: AclRulesCommand::class))
            ->expectsTable(
                headers: ['Key', 'Title', 'Description', 'Area'],
                rows: $rows,
            )
            ->expectsExitCode(0)
            ->execute($container);
    }
}