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

namespace Tobento\App\User\Console;

use Tobento\Service\Console\AbstractCommand;
use Tobento\Service\Console\InteractorInterface;
use Tobento\Service\Acl\AclInterface;

/**
 * AclRulesCommand
 */
class AclRulesCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        acl:rules | List all acl rules.
    ';

    /**
     * Handle the command.
     *
     * @param InteractorInterface $io
     * @param AclInterface $acl
     * @return int The exit status code: 
     *     0 SUCCESS
     *     1 FAILURE If some error happened during the execution
     *     2 INVALID To indicate incorrect command usage e.g. invalid options
     */
    public function handle(InteractorInterface $io, AclInterface $acl): int
    {
        $rows = [];
        
        foreach($acl->getRules() as $rule) {
            $rows[] = [
                $rule->getKey(),
                $rule->getTitle(),
                $rule->getDescription(),
                $rule->getArea(),
            ];
        }
        
        $io->table(
            headers: ['Key', 'Title', 'Description', 'Area'],
            rows: $rows,
        );
        
        return static::SUCCESS;
    }
}