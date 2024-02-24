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

namespace Tobento\App\User\Migration;

use Tobento\Service\Migration\MigrationInterface;
use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\Actions;
use Tobento\Service\Repository\Storage\Migration\RepositoryAction;
use Tobento\Service\Repository\Storage\Migration\RepositoryDeleteAction;
use Tobento\App\User\Authentication\Token\TokenStoragesInterface;
use Tobento\App\User\Authentication\Token\RepositoryStorage;
use Tobento\App\User\Authentication\Token\TokenRepository;

/**
 * AuthenticationTokenRepository
 */
class AuthenticationTokenRepository implements MigrationInterface
{
    /**
     * Create a new AuthenticationTokenRepository.
     *
     * @param TokenStoragesInterface $tokenStorages
     */
    public function __construct(
        protected TokenStoragesInterface $tokenStorages,
    ) {}
    
    /**
     * Return a description of the migration.
     *
     * @return string
     */
    public function description(): string
    {
        return 'Authentication token repository';
    }
        
    /**
     * Return the actions to be processed on install.
     *
     * @return ActionsInterface
     */
    public function install(): ActionsInterface
    {
        $actions = [];

        foreach($this->tokenStorages->names() as $name) {
            $storage = $this->tokenStorages->get($name);
            
            if ($storage instanceof RepositoryStorage) {
                $actions[] = RepositoryAction::newOrNull(
                    repository: $storage->repository(),
                    description: sprintf('Authentication token repository %s', $storage->name()),
                );
            }
        }
        
        return new Actions(...$actions);
    }

    /**
     * Return the actions to be processed on uninstall.
     *
     * @return ActionsInterface
     */
    public function uninstall(): ActionsInterface
    {
        $actions = [];

        foreach($this->tokenStorages->names() as $name) {
            $storage = $this->tokenStorages->get($name);
            
            if ($storage instanceof RepositoryStorage) {
                $actions[] = RepositoryDeleteAction::newOrNull(
                    repository: $storage->repository(),
                    description: sprintf('Authentication token repository %s', $storage->name()),
                );
            }
        }
        
        return new Actions(...$actions);
    }
}