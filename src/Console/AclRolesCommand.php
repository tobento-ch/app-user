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
 * AclRolesCommand
 */
class AclRolesCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        acl:roles | List all acl roles.
    ';

    /**
     * Handle the command.
     *
     * @param InteractorInterface $io
     * @param TokenStoragesInterface $tokenStorages
     * @return int The exit status code: 
     *     0 SUCCESS
     *     1 FAILURE If some error happened during the execution
     *     2 INVALID To indicate incorrect command usage e.g. invalid options
     */
    public function handle(InteractorInterface $io, AclInterface $acl): int
    {
        $rows = [];
        
        foreach($acl->getRoles() as $role) {
            $rows[] = [
                $role->key(),
                $role->name(),
                $role->active(),
                implode(', ', $role->areas()),
                implode(', ', $role->getPermissions()),
            ];
        }
        
        $io->table(
            headers: ['Key', 'Name', 'Active', 'Areas', 'Permissions'],
            rows: $rows,
        );
        
        return static::SUCCESS;
    }
}