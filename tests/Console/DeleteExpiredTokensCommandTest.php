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
use Tobento\Service\Console\Test\TestCommand;
use Tobento\App\User\Console\DeleteExpiredTokensCommand;
use Tobento\App\User\Authentication\Token\TokenStoragesInterface;
use Tobento\App\User\Authentication\Token\TokenStorages;
use Tobento\App\User\Authentication\Token\ServiceStorage;
use Tobento\App\User\Authentication\Token\InMemoryStorage as TokenInMemoryStorage;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\Service\Container\Container;
use Tobento\Service\Clock\FrozenClock;
    
class DeleteExpiredTokensCommandTest extends TestCase
{    
    public function testDeleteSuccess()
    {        
        $container = new Container();
        $container->set(TokenStoragesInterface::class, function() {
            return new TokenStorages(
                new ServiceStorage(
                    clock: new FrozenClock(),
                    storage: new InMemoryStorage([]),
                    table: 'tokens_foo',
                ),
                new ServiceStorage(
                    clock: new FrozenClock(),
                    storage: new InMemoryStorage([]),
                    table: 'tokens_bar',
                ),
            );
        });
        
        (new TestCommand(command: DeleteExpiredTokensCommand::class))
            ->expectsOutput('deleted expired tokens from storage:tokens_foo storage')
            ->expectsOutput('deleted expired tokens from storage:tokens_bar storage')
            ->expectsExitCode(0)
            ->execute($container);
    }
    
    public function testDeleteNoticeIfDoesNotSupportDeletion()
    {        
        $container = new Container();
        $container->set(TokenStoragesInterface::class, function() {
            return new TokenStorages(
                new TokenInMemoryStorage(
                    clock: new FrozenClock(),
                ),
            );
        });
        
        (new TestCommand(command: DeleteExpiredTokensCommand::class))
            ->expectsOutput('storage inmemory does not support deleting expired tokens')
            ->expectsExitCode(0)
            ->execute($container);
    }
    
    public function testDeletesSpecificStorageTokens()
    {        
        $container = new Container();
        $container->set(TokenStoragesInterface::class, function() {
            return new TokenStorages(
                new ServiceStorage(
                    clock: new FrozenClock(),
                    storage: new InMemoryStorage([]),
                    table: 'tokens_foo',
                ),
                new ServiceStorage(
                    clock: new FrozenClock(),
                    storage: new InMemoryStorage([]),
                    table: 'tokens_bar',
                ),
            );
        });
        
        (new TestCommand(
            command: DeleteExpiredTokensCommand::class,
            input: [
                '--storage' => ['storage:tokens_foo'],
            ],
        ))
        ->expectsOutput('deleted expired tokens from storage:tokens_foo storage')
        ->doesntExpectOutputToContain('storage:tokens_bar')
        ->expectsExitCode(0)
        ->execute($container);
    }
}