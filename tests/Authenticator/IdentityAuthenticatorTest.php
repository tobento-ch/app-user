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

namespace Tobento\App\User\Test\Authenticator;

use PHPUnit\Framework\TestCase;
use Tobento\App\User\Test\Factory;
use Tobento\App\User\Authenticator\IdentityAuthenticator;
use Tobento\App\User\Authenticator\AuthenticatorInterface;
use Tobento\App\User\Authenticator\UserVerifiers;
use Tobento\App\User\Authenticator\UserRoleVerifier;
use Tobento\App\User\Exception\AuthenticationException;
use Nyholm\Psr7\Factory\Psr17Factory;
    
/**
 * IdentityAuthenticatorTest
 */
class IdentityAuthenticatorTest extends TestCase
{
    public function testConstructMethod()
    {
        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
        );

        $this->assertInstanceOf(AuthenticatorInterface::class, $authenticator);
        $this->assertSame(['email', 'username', 'smartphone', 'password'], $authenticator->getIdentifyBy());
        $this->assertSame('user', $authenticator->getUserInputName());
        $this->assertSame('password', $authenticator->getPasswordInputName());
        $this->assertSame('POST', $authenticator->getRequestMethod());
    }
    
    public function testConstructMethodWithDefinedParameters()
    {
        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
            identifyBy: ['email'],
            userInputName: 'name',
            passwordInputName: 'pw',
            requestMethod: 'GET',
        );
        
        $this->assertSame(['email'], $authenticator->getIdentifyBy());
        $this->assertSame('name', $authenticator->getUserInputName());
        $this->assertSame('pw', $authenticator->getPasswordInputName());
        $this->assertSame('GET', $authenticator->getRequestMethod());
    }
    
    public function testIdentifyByMethod()
    {
        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
        );
        
        $authenticator->identifyBy(['email', 'password']);
        
        $this->assertSame(['email', 'password'], $authenticator->getIdentifyBy());
    }
    
    public function testUserInputNameMethod()
    {
        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
        );
        
        $authenticator->userInputName('usr');
        
        $this->assertSame('usr', $authenticator->getUserInputName());
    }
    
    public function testPasswordInputNameMethod()
    {
        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
        );
        
        $authenticator->passwordInputName('pw');
        
        $this->assertSame('pw', $authenticator->getPasswordInputName());
    }
    
    public function testRequestMethodMethod()
    {
        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
        );
        
        $authenticator->requestMethod('GET');
        
        $this->assertSame('GET', $authenticator->getRequestMethod());
    }
    
    public function testAuthenticateWithEmailAndPassword()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => 'tom@example.com', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateWithUsernameAndPassword()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['username' => 'tom', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => 'tom', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateWithSmartphoneAndPassword()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['smartphone' => '555-33-66', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => '555-33-66', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticatePutMethod()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['smartphone' => '555-33-66', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->requestMethod('PUT');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'PUT', uri: 'foo')
            ->withParsedBody(['user' => '555-33-66', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateGetMethod()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->requestMethod('GET');
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo?user=tom@example.com&password=123456'
        );
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateFailsIfNoInputPassword()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => 'tom@example.com']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfInvalidPassword()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => 'tom@example.com', 'password' => '23456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfEmptyPassword()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => 'tom@example.com', 'password' => '']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfNoInputUser()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['password' => '123456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfUserDoesNotExist()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();
        
        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => 'james@example.com', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfNoInputAtAll()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();
        
        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo');
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfWrongRequestMethod()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();
        
        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->requestMethod('GET');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => 'james@example.com', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateWithUserVerifier()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                [
                    'email' => 'tom@example.com',
                    'password' => $passwordHasher->hash('123456'),
                    'role_key' => 'editor',
                ],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticatorNew = $authenticator->withUserVerifier(
            new UserVerifiers(
                new UserRoleVerifier('editor'),
            ),
        );
        
        $this->assertNotSame($authenticatorNew, $authenticator);
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => 'tom@example.com', 'password' => '123456']);
        
        $user = $authenticatorNew->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateFailsIfUserVerifierFails()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new IdentityAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                [
                    'email' => 'tom@example.com',
                    'password' => $passwordHasher->hash('123456'),
                    'role_key' => 'editor',
                ],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticatorNew = $authenticator->withUserVerifier(
            new UserVerifiers(
                new UserRoleVerifier('administrator'),
            ),
        );
        
        $this->assertNotSame($authenticatorNew, $authenticator);
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['user' => 'tom@example.com', 'password' => '123456']);
        
        $user = $authenticatorNew->authenticate($request);
    }
}