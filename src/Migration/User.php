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
use Tobento\Service\Migration\Action\FilesCopy;
use Tobento\Service\Migration\Action\FilesDelete;
use Tobento\Service\Migration\Action\FileStringReplacer;
use Tobento\Service\Dir\DirsInterface;

/**
 * User
 */
class User implements MigrationInterface
{
    /**
     * @var array The files.
     */    
    protected array $files;
    
    /**
     * Create a new Migration.
     *
     * @param DirsInterface $dirs
     */    
    public function __construct(
        protected DirsInterface $dirs,
    ) {
        $vendor = realpath(__DIR__.'/../../');
        
        $this->files = [
            $this->dirs->get('config') => [
                $vendor.'/config/user.php',
            ],
        ];
    }
    
    /**
     * Return a description of the migration.
     *
     * @return string
     */    
    public function description(): string
    {
        return 'User config file.';
    }
        
    /**
     * Return the actions to be processed on install.
     *
     * @return ActionsInterface
     */    
    public function install(): ActionsInterface
    {        
        return new Actions(
            new FilesCopy($this->files),
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
            new FilesDelete($this->files),
        );
    }
}