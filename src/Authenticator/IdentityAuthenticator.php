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

namespace Tobento\App\User\Authenticator;

use Tobento\App\User\UserInterface;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\App\User\PasswordHasherInterface;
use Tobento\App\User\Exception\AuthenticationException;
use Tobento\Service\Validation\ValidatorInterface;
use Tobento\Service\Requester\Requester;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authenticates user by identity attributes.
 */
class IdentityAuthenticator implements AuthenticatorInterface
{
    /**
     * @var array<int, string>
     */
    protected array $identifyBy = ['email', 'username', 'smartphone', 'password'];
    
    /**
     * @var string
     */
    protected string $userInputName = 'user';
    
    /**
     * @var string
     */
    protected string $passwordInputName = 'password';
    
    /**
     * @var string
     */
    protected string $requestMethod = 'POST';
    
    /**
     * Create a new IdentityAuthenticator.
     *
     * @param UserRepositoryInterface $userRepository
     * @param ValidatorInterface $validator
     * @param PasswordHasherInterface $passwordHasher
     * @param null|UserVerifierInterface $userVerifier
     * @param null|array $identifyBy
     * @param string $userInputName
     * @param string $passwordInputName
     * @param null|string $requestMethod
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected ValidatorInterface $validator,
        protected PasswordHasherInterface $passwordHasher,
        protected null|UserVerifierInterface $userVerifier = null,
        null|array $identifyBy = null,
        string $userInputName = 'user',
        string $passwordInputName = 'password',
        null|string $requestMethod = null,
    ) {
        if (!is_null($identifyBy)) {
            $this->identifyBy($identifyBy);
        }
        
        $this->userInputName($userInputName);
        $this->passwordInputName($passwordInputName);
        
        if (!is_null($requestMethod)) {
            $this->requestMethod($requestMethod);
        }
    }
    
    /**
     * Authenticate user.
     *
     * @param ServerRequestInterface $request
     * @return UserInterface
     * @throws AuthenticationException If authentication fails.
     */
    public function authenticate(ServerRequestInterface $request): UserInterface
    {
        $requester = new Requester($request);
        
        if ($requester->method() !== $this->requestMethod) {
            throw new AuthenticationException('Invalid request method');
        }
        
        $user = $requester->input()->get($this->userInputName);
        $password = $requester->input()->get($this->passwordInputName);
        
        // validate user:
        if (! $this->validator->validating(
            value: $user,
            rules: 'required|string|minLen:3|maxLen:150',
        )->isValid()) {
            throw new AuthenticationException('Invalid user');
        }
        
        // validate password:
        if (in_array('password', $this->identifyBy)) {
            if (! $this->validator->validating(
                value: $password,
                rules: 'required|string',
            )->isValid()) {
                throw new AuthenticationException('Invalid password');
            }
        }
        
        // find user:   
        $identifyBy = array_fill_keys($this->identifyBy, $user);
        unset($identifyBy['password']);
        
        $user = $this->userRepository->findByIdentity(...$identifyBy);
        
        if (is_null($user)) {
            throw new AuthenticationException('User not found');
        }
        
        // verify user:
        if (!is_null($this->userVerifier)) {
            $this->userVerifier->verify($user);
        }
        
        // verify password:
        if (in_array('password', $this->identifyBy)) {
            if (! $this->passwordHasher->verify(hashedPassword: $user->password(), plainPassword: $password)) {
                throw new AuthenticationException('Invalid password');
            }
        }
        
        return $user;
    }
    
    /**
     * Sets the identityBy.
     *
     * @param array<int, string> $identifyBy
     * @return static $this
     * @throws AuthenticationException If invalid values.
     */
    public function identifyBy(array $identifyBy): static
    {
        $valid = ['email', 'username', 'smartphone', 'password'];
        
        if (!empty(array_diff($identifyBy, $valid))) {
            throw new AuthenticationException(
                'Only email, username, smartphone and password are valid values for identifyBy parameter'
            );
        }
        
        if (count($identifyBy) === 1 && $identifyBy[0] === 'password') {
            $identifyBy = [];
        }
        
        if (empty($identifyBy)) {
            throw new AuthenticationException(
                'identifyBy parameter must have at least one of email, username or smartphone'
            );
        }
        
        $this->identifyBy = $identifyBy;
        return $this;
    }
    
    /**
     * Returns the identityBy.
     *
     * @return array<int, string>
     */
    public function getIdentifyBy(): array
    {
        return $this->identifyBy;
    }
    
    /**
     * Returns the true if is identified by the name specified, otherwise false.
     *
     * @return bool
     */
    public function isIdentifiedBy(string $name): bool
    {
        return array_key_exists($name, $this->identifyBy);
    }
    
    /**
     * Returns a new instance with the specified user verifier.
     *
     * @param UserVerifierInterface $verifier
     * @return static
     */
    public function withUserVerifier(UserVerifierInterface $verifier): static
    {
        $new = clone $this;
        $new->userVerifier = $verifier;
        return $new;
    }

    /**
     * Sets the userInputName.
     *
     * @param string $requestMethod
     * @return static $this
     */
    public function userInputName(string $userInputName): static
    {
        $this->userInputName = $userInputName;
        return $this;
    }
    
    /**
     * Returns the userInputName.
     *
     * @return string
     */
    public function getUserInputName(): string
    {
        return $this->userInputName;
    }
    
    /**
     * Sets the passwordInputName.
     *
     * @param string $requestMethod
     * @return static $this
     */
    public function passwordInputName(string $passwordInputName): static
    {
        $this->passwordInputName = $passwordInputName;
        return $this;
    }
    
    /**
     * Returns the passwordInputName.
     *
     * @return string
     */
    public function getPasswordInputName(): string
    {
        return $this->passwordInputName;
    }
    
    /**
     * Sets the request method.
     *
     * @param string $requestMethod
     * @return static $this
     * @throws AuthenticationException If invalid method.
     */
    public function requestMethod(string $requestMethod): static
    {
        $requestMethod = strtoupper($requestMethod);
        
        if (!in_array($requestMethod, ['POST', 'GET', 'PUT'])) {
            throw new AuthenticationException('Only POST, GET and PUT method is allowed');
        }
        
        $this->requestMethod = $requestMethod;
        return $this;
    }
    
    /**
     * Returns the requestMethod.
     *
     * @return string
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }
}