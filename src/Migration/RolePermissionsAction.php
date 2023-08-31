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

use Tobento\Service\Migration\ActionInterface;
use Tobento\Service\Migration\ActionFailedException;
use Tobento\Service\Resource\ResourceInterface;
use Tobento\App\User\RoleRepositoryInterface;

/**
 * RolePermissionsAction
 */
class RolePermissionsAction implements ActionInterface
{
    /**
     * Create a new RolePermissionsAction.
     *
     * @param RoleRepositoryInterface $roleRepository
     * @param null|array $add
     * @param null|array $remove
     * @param string $description
     */
    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
        protected null|array $add = null,
        protected null|array $remove = null,
        protected string $description = '',
    ) {}
    
    /**
     * Returns a name of the action.
     *
     * @return string
     */
    public function name(): string
    {
        return $this::class;
    }

    /**
     * Returns a description of the action.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }
    
    /**
     * Process the action.
     *
     * @return void
     * @throws ActionFailedException
     */
    public function process(): void
    {
        if (!empty($this->add)) {
            $this->processAddPermissions($this->add);
        }
        
        if (!empty($this->remove)) {
            $this->processRemovePermissions($this->remove);
        }
    }
    
    /**
     * Returns the processed data information.
     *
     * @return array<array-key, string>
     */
    public function processedDataInfo(): array
    {
        $data = [];
        
        if (!empty($this->add)) {
            foreach($this->add as $roleKey => $permissions) {
                $data['permissions added for role: '.$roleKey] = implode(', ', $permissions);
            }
        }
        
        if (!empty($this->remove)) {
            foreach($this->remove as $roleKey => $permissions) {
                $data['permissions removed for role: '.$roleKey] = implode(', ', $permissions);
            }
        }
        
        return $data;
    }
    
    /**
     * Processes add permissions.
     *
     * @param array $rolesPermissions
     * @return void
     */
    protected function processAddPermissions(array $rolesPermissions): void
    {
        foreach($rolesPermissions as $roleKey => $permissions) {
            
            $role = $this->roleRepository->findByKey(key: $roleKey);
            
            if (is_null($role)) {
                continue;
            }
                        
            $role->addPermissions($permissions);

            $this->roleRepository->update(
                where: ['key' => $roleKey],
                attributes: ['permissions' => $role->getPermissions()],
            );
        }
    }
    
    /**
     * Processes remove permissions.
     *
     * @param array $rolesPermissions
     * @return void
     */
    protected function processRemovePermissions(array $rolesPermissions): void
    {
        foreach($rolesPermissions as $roleKey => $permissions) {
            
            $role = $this->roleRepository->findByKey(key: $roleKey);
            
            if (is_null($role)) {
                continue;
            }
                        
            $role->removePermissions($permissions);
            
            $this->roleRepository->update(
                where: ['key' => $roleKey],
                attributes: ['permissions' => $role->getPermissions()],
            );
        }
    }    
}