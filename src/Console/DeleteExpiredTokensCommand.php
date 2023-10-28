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
use Tobento\App\User\Authentication\Token\TokenStoragesInterface;
use Tobento\App\User\Authentication\Token\CanDeleteExpiredTokens;

/**
 * DeleteExpiredTokensCommand
 */
class DeleteExpiredTokensCommand extends AbstractCommand
{
    /**
     * The signature of the console command.
     */
    public const SIGNATURE = '
        auth:purge-tokens | Deletes expired tokens from token storages.
        {--storage[] : Specific storage names to delete tokens.}
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
    public function handle(InteractorInterface $io, TokenStoragesInterface $tokenStorages): int
    {
        $storageNames = $io->option('storage');
        
        if (empty($storageNames)) {
            $storageNames = $tokenStorages->names();
        }
        
        foreach($storageNames as $storageName) {
            
            if (! $tokenStorages->has($storageName)) {
                continue;   
            }
            
            $storage = $tokenStorages->get($storageName);

            if ($storage instanceof CanDeleteExpiredTokens) {
                $deleted = $storage->deleteExpiredTokens();
                
                if ($deleted) {
                    $io->success(sprintf(
                        'deleted expired tokens from %s storage',
                        $storageName,
                    ));
                } else {
                    $io->error(sprintf(
                        'deleted expired tokens from %s storage failed',
                        $storageName,
                    ));
                }
            } else {
                $io->info(sprintf(
                    'storage %s does not support deleting expired tokens',
                    $storageName,
                ));
            }
        }
        
        return static::SUCCESS;
    }
}