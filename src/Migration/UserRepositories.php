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
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\AddressRepositoryInterface;

/**
 * UserRepositories
 */
class UserRepositories implements MigrationInterface
{
    /**
     * Create a new UserRepositories.
     *
     * @param UserRepositoryInterface $userRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected AddressRepositoryInterface $addressRepository,
    ) {}
    
    /**
     * Return a description of the migration.
     *
     * @return string
     */
    public function description(): string
    {
        return 'User and address resources.';
    }
        
    /**
     * Return the actions to be processed on install.
     *
     * @return ActionsInterface
     */
    public function install(): ActionsInterface
    {
        return new Actions(
            RepositoryAction::newOrNull(
                repository: $this->userRepository,
                description: 'User resource',
            ),
            RepositoryAction::newOrNull(
                repository: $this->addressRepository,
                description: 'Address resource',
            ),
        );
    }

    /**
     * Return the actions to be processed on uninstall.
     *
     * @return ActionsInterface
     */
    public function uninstall(): ActionsInterface
    {
        return new Actions(
            RepositoryDeleteAction::newOrNull(
                repository: $this->userRepository,
                description: 'User resource',
            ),
            RepositoryDeleteAction::newOrNull(
                repository: $this->addressRepository,
                description: 'Address resource',
            ),
        );
    }
}