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

/**
 * UserStorageRepository
 */
class UserStorageRepository extends StorageRepository implements UserRepositoryInterface
{
    /**
     * Returns the configured columns.
     *
     * @return iterable<ColumnInterface>|ColumnsInterface
     */
    protected function configureColumns(): iterable|ColumnsInterface
    {
        return [
            Column\Id::new(),
            Column\Text::new('role_key'),
            Column\Text::new('number'),
            Column\Boolean::new('active'),
            Column\Text::new('type'),
            Column\Text::new('password'),
            Column\Text::new('username'),
            Column\Text::new('email'),
            Column\Text::new('smartphone')->type(length: 15),
            Column\Text::new('locale', type: 'char')->type(length: 5),
            Column\Datetime::new('birthday', type: 'date'),
            Column\Datetime::new('date_created'),
            Column\Datetime::new('date_updated'),
            Column\Datetime::new('date_last_visited'),
            Column\Json::new('image'),
            Column\Boolean::new('newsletter'),
            Column\Json::new('permissions'),
        ];
    }
    
    /**
     * Returns the found entity using the specified where parameters
     * or null if none found.
     *
     * @param array $where
     * @return null|UserInterface
     * @throws RepositoryReadException
     */
    public function findOne(array $where = []): null|UserInterface
    {
        return parent::findOne(where: $where);
    }
    
    /**
     * Returns the found entity using the specified id (primary key)
     * or null if none found.
     *
     * @param int|string $id
     * @return null|UserInterface
     * @throws RepositoryReadException
     */
    public function findById(int|string $id): null|UserInterface
    {
        return parent::findById(id: $id);
    }
    
    /**
     * Returns the found user using the specified unique identity parameters
     * usually used for login or null if none found.
     *
     * @param string $email
     * @param string $username
     * @param string $smartphone
     * @return null|UserInterface
     */
    public function findByIdentity(string $email = '', string $username = '', string $smartphone = ''): null|UserInterface
    {
        $query = $this->query();
        
        if ($email === '' && $username === '' && $smartphone === '') {
            return null;
        }
        
        if ($email !== '') {
            $query->orWhere('email', '=', $email);
        }
        
        if ($username !== '') {
            $query->orWhere('username', '=', $username);
        }
        
        if ($smartphone !== '') {
            $query->orWhere('smartphone', '=', $smartphone);
        }
        
        return $this->createEntityOrNull(
            item: $query->first(),
        );
    }
}