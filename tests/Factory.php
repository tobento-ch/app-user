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

namespace Tobento\App\User\Test;

use Tobento\App\User\UserFactoryInterface;
use Tobento\App\User\UserFactory;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\UserStorageRepository;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\App\User\AddressRepositoryInterface;
use Tobento\App\User\AddressStorageRepository;
use Tobento\App\User\AddressFactoryInterface;
use Tobento\App\User\AddressFactory;
use Tobento\App\User\RoleFactoryInterface;
use Tobento\App\User\RoleFactory;
use Tobento\App\User\RoleRepositoryInterface;
use Tobento\App\User\RoleStorageRepository;
use Tobento\App\User\PasswordHasherInterface;
use Tobento\App\User\PasswordHasher;
use Tobento\Service\User\AddressesFactory;
use Tobento\Service\User\AddressFactory as ServiceAddressFactory;
use Tobento\Service\Country\CountryRepository;
use Tobento\Service\Validation\ValidatorInterface;
use Tobento\Service\Validation\Validator;
use Tobento\Service\Session\Session;
use Tobento\Service\Session\SessionInterface;
use Tobento\Service\Acl\AclInterface;
use Tobento\Service\Acl\Acl;
use Tobento\Service\Acl\Role;

/**
 * Factory
 */
class Factory
{
    public static function createUserFactory(
        null|AddressRepositoryInterface $addressRepository = null,
    ): UserFactoryInterface {
        return new UserFactory(
            acl: static::createAcl(),
            addressRepository: $addressRepository ?: static::createAddressRepository(),
            addressFactory: static::createAddressFactory(),
        );
    }
    
    public static function createUserRepository(array $users = []): UserRepositoryInterface
    {
        $addressRepo = static::createAddressRepository();
        
        $repository = new UserStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'users',
            userFactory: static::createUserFactory($addressRepo),
            addressRepository: $addressRepo,
        );
        
        foreach($users as $user) {
            $repository->create($user);
        }
        
        return $repository;
    }

    public static function createAddressFactory(string $locale = 'en'): AddressFactoryInterface
    {
        return new AddressFactory(
            addressFactory: new ServiceAddressFactory(),
            addressesFactory: new AddressesFactory(new ServiceAddressFactory()),
            countryRepository: new CountryRepository(locale: $locale),
        );
    }
    
    public static function createAddressRepository(
        array $addresses = [],
        string $locale = 'en',
    ): AddressRepositoryInterface {
        $repository = new AddressStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'addresses',
            addressFactory: static::createAddressFactory($locale),
        );
        
        foreach($addresses as $address) {
            $repository->create($address);
        }
        
        return $repository;
    }
    
    public static function createRoleFactory(): RoleFactoryInterface
    {
        return new RoleFactory();
    }
    
    public static function createRoleRepository(array $roles = []): RoleRepositoryInterface
    {
        $repository = new RoleStorageRepository(
            storage: new InMemoryStorage([]),
            table: 'roles',
            entityFactory: static::createRoleFactory(),
        );
        
        foreach($roles as $roles) {
            $repository->create($roles);
        }
        
        return $repository;
    }
    
    public static function createAcl(bool $withDefaultRoles = true): AclInterface
    {
        $acl = new Acl();
        
        if ($withDefaultRoles) {
            $acl->setRoles([
                new Role('guest', ['frontend']),
                new Role('registered', ['frontend']),
                new Role('administrator', ['backend']),
                new Role('editor', ['backend']),
            ]);            
        }
        
        return $acl;
    }
    
    public static function createPasswordHasher(): PasswordHasherInterface
    {
        return new PasswordHasher();
    }
    
    public static function createValidator(): ValidatorInterface
    {
        return new Validator();
    }
    
    public static function createSession(string $name = 'sess'): SessionInterface
    {
        return new Session(
            name: $name,
            maxlifetime: 1800,
            cookiePath: '/',
            cookieDomain: '',
            cookieSamesite: 'Strict',
            secure: true,
            httpOnly: true,
            saveHandler: null,
        );
    }
}