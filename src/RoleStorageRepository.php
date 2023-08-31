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

namespace Tobento\App\User;

use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\Column\ColumnsInterface;
use Tobento\Service\Repository\Storage\Column\ColumnInterface;
use Tobento\Service\Repository\Storage\Column;
use Tobento\Service\Repository\RepositoryReadException;
use Tobento\Service\Acl\RoleInterface;

/**
 * RoleStorageRepository
 */
class RoleStorageRepository extends StorageRepository implements RoleRepositoryInterface
{
    /**
     * Returns the configured columns.
     *
     * @return iterable<ColumnInterface>|ColumnsInterface
     */
    protected function configureColumns(): iterable|ColumnsInterface
    {
        return [
            //Column\Id::new(),
            Column\Text::new('key'),
            Column\Boolean::new('active'),
            Column\Text::new('name'),
            Column\Json::new('areas'),
            Column\Json::new('permissions'),
        ];
    }
    
    /**
     * Returns the found entity using the specified where parameters
     * or null if none found.
     *
     * @param array $where
     * @return null|RoleInterface
     * @throws RepositoryReadException
     */
    public function findOne(array $where = []): null|RoleInterface
    {
        return parent::findOne(where: $where);
    }
    
    /**
     * Returns the found role using the specified key
     * or null if none found.
     *
     * @param string $key The key such as 'editor'
     * @return null|RoleInterface
     */
    public function findByKey(string $key): null|RoleInterface
    {
        return $this->findOne(where: ['key' => $key]);
    }
}