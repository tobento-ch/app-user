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
use Tobento\App\User\Authenticator\AttributesAuthenticator;
use Tobento\App\User\Authenticator\AuthenticatorInterface;
use Tobento\App\User\Authenticator\UserVerifiers;
use Tobento\App\User\Authenticator\UserRoleVerifier;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\App\User\Exception\AuthenticationValidationException;
use Nyholm\Psr7\Factory\Psr17Factory;

class AttributesAuthenticatorTest extends TestCase
{
    public function testConstructMethod()
    {
        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
        );

        $this->assertInstanceOf(AuthenticatorInterface::class, $authenticator);
        $this->assertSame([], $authenticator->getAttributes());
        $this->assertSame('POST', $authenticator->getRequestMethod());
    }
    
    public function testConstructMethodWithDefinedParameters()
    {
        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
            requestMethod: 'GET',
        );
        
        $this->assertSame([], $authenticator->getAttributes());
        $this->assertSame('GET', $authenticator->getRequestMethod());
    }
    
    public function testGetAttributesMethod()
    {
        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
        );
        
        $authenticator->addAttribute(name: 'email');
        
        $this->assertSame(['email' => [null, null]], $authenticator->getAttributes());
    }
    
    public function testHasAttributeMethod()
    {
        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
        );
        
        $authenticator->addAttribute(name: 'email');
        
        $this->assertTrue($authenticator->hasAttribute('email'));
        $this->assertFalse($authenticator->hasAttribute('username'));
    }    
    
    public function testRequestMethodMethod()
    {
        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(),
            validator: Factory::createValidator(),
            passwordHasher: Factory::createPasswordHasher(),
        );
        
        $authenticator->requestMethod('GET');
        
        $this->assertSame('GET', $authenticator->getRequestMethod());
    }
    
    public function testAuthenticateFailsNotAttributeIsSet()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsWithOnlyPasswordAttribute()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'password');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfInvalidPassword()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        $authenticator->addAttribute(name: 'password');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com', 'password' => '23456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsWithMissingInput()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['password' => '23456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfNoInputAtAll()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo');
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfUserDoesNotExist()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        $authenticator->addAttribute(name: 'password');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'james@example.com', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfWrongRequestWrong()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->requestMethod('GET');
        $authenticator->addAttribute(name: 'email');
        $authenticator->addAttribute(name: 'password');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfInvalidAttribute()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        $authenticator->addAttribute(name: 'password');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'james@example.com', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateFailsIfAnyAttributeIsInvalid()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                [
                    'email' => 'tom@example.com',
                    'smartphone' => '555-33-66',
                    'password' => $passwordHasher->hash('123456')
                ],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        $authenticator->addAttribute(name: 'smartphone');
        $authenticator->addAttribute(name: 'password');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'james@example.com', 'smartphone' => '555-33-66', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateWithEmailAndPassword()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        $authenticator->addAttribute(name: 'password');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateWithMulitpleAttributes()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                [
                    'email' => 'tom@example.com',
                    'smartphone' => '555-33-66',
                    'password' => $passwordHasher->hash('123456')
                ],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        $authenticator->addAttribute(name: 'smartphone');
        $authenticator->addAttribute(name: 'password');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com', 'smartphone' => '555-33-66', 'password' => '123456']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateWithOneAttribute()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateWithPutMethod()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->requestMethod('PUT');
        $authenticator->addAttribute(name: 'email');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'PUT', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateWithGetMethod()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->requestMethod('GET');
        $authenticator->addAttribute(name: 'email');
        
        $request = (new Psr17Factory())->createServerRequest(
            method: 'GET',
            uri: 'foo?email=tom@example.com'
        );
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }    
    
    public function testAuthenticateWithCustomInputName()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email', inputName: 'mail');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['mail' => 'tom@example.com']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateFailsIfCustomInputName()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email', inputName: 'mail');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com']);
        
        $user = $authenticator->authenticate($request);
    }

    public function testAuthenticateWithCustomValidate()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['smartphone' => '55553366', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'smartphone', validate: 'required|digit|minLen:3|maxLen:150');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['smartphone' => '55553366']);
        
        $user = $authenticator->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateFailsIfCustomValidateFails()
    {
        $this->expectException(AuthenticationValidationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['smartphone' => '55553366', 'password' => $passwordHasher->hash('123456')],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'smartphone', validate: 'required|digit|minLen:3|maxLen:150');
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['smartphone' => '555-44']);
        
        $user = $authenticator->authenticate($request);
    }
    
    public function testAuthenticateWithUserVerifier()
    {
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'role_key' => 'editor'],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        
        $authenticatorNew = $authenticator->withUserVerifier(
            new UserVerifiers(
                new UserRoleVerifier('editor'),
            ),
        );
        
        $this->assertNotSame($authenticatorNew, $authenticator);
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com']);
        
        $user = $authenticatorNew->authenticate($request);
        
        $this->assertTrue(true);
    }
    
    public function testAuthenticateWithUserVerifierFails()
    {
        $this->expectException(AuthenticationException::class);
        
        $passwordHasher = Factory::createPasswordHasher();

        $authenticator = new AttributesAuthenticator(
            userRepository: Factory::createUserRepository(users: [
                ['email' => 'tom@example.com', 'role_key' => 'editor'],
            ]),
            validator: Factory::createValidator(),
            passwordHasher: $passwordHasher,
        );
        
        $authenticator->addAttribute(name: 'email');
        
        $authenticatorNew = $authenticator->withUserVerifier(
            new UserVerifiers(
                new UserRoleVerifier('administrator'),
            ),
        );
        
        $this->assertNotSame($authenticatorNew, $authenticator);
        
        $request = (new Psr17Factory())->createServerRequest(method: 'POST', uri: 'foo')
            ->withParsedBody(['email' => 'tom@example.com']);
        
        $user = $authenticatorNew->authenticate($request);
    }
}