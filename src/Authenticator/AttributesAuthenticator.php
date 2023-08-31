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
use Tobento\App\User\Exception\AuthenticationValidationException;
use Tobento\Service\Validation\ValidatorInterface;
use Tobento\Service\Requester\Requester;
use Psr\Http\Message\ServerRequestInterface;

/**
 * AttributesAuthenticator
 */
class AttributesAuthenticator implements AuthenticatorInterface
{
    /**
     * @var string
     */
    protected string $requestMethod = 'POST';
    
    /**
     * @var array
     */
    protected array $attributes = [];
    
    /**
     * Create a new LoginAuthenticator.
     *
     * @param UserRepositoryInterface $userRepository
     * @param ValidatorInterface $validator
     * @param PasswordHasherInterface $passwordHasher
     * @param null|UserVerifierInterface $userVerifier
     * @param null|string $requestMethod
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected ValidatorInterface $validator,
        protected PasswordHasherInterface $passwordHasher,
        protected null|UserVerifierInterface $userVerifier = null,
        null|string $requestMethod = null,
    ) {
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
        
        $where = [];
        $rules = [];
        
        foreach($this->attributes as $name => [$inputName, $validate]) {
            $inputName = $inputName ?: $name;
            $rules[$inputName] = $validate ?: 'required|string|minLen:3|maxLen:150';
            
            if ($name !== 'password') {
                $where[$name] = $requester->input()->get($inputName);
            }
        }
        
        $validation = $this->validator->validate(
            data: $requester->input()->all(),
            rules: $rules,
        );
        
        if (! $validation->isValid()) {
            throw new AuthenticationValidationException($validation);
        }
        
        if (empty($where)) {
            throw new AuthenticationException('Unable to identify user');
        }
        
        // find user:
        $user = $this->userRepository->findOne(where: $where);

        if (is_null($user)) {
            throw new AuthenticationException('User not found');
        }
        
        // verify user:
        if (!is_null($this->userVerifier)) {
            $this->userVerifier->verify($user);
        }
        
        // verify password if attribute is specified:
        if (isset($this->attributes['password'])) {
            
            $inputName = $this->attributes['password'][0] ?? 'password';
            $password = $requester->input()->get($inputName);
            
            if (! $this->passwordHasher->verify(hashedPassword: $user->password(), plainPassword: (string)$password)) {
                throw new AuthenticationException('Invalid password');
            }
        }
        
        return $user;
    }
    
    /**
     * Add an attribute.
     *
     * @param string $name
     * @param null|string $inputName
     * @param null|string|array $validate
     * @return static $this
     */
    public function addAttribute(
        string $name,
        null|string $inputName = null,
        null|string|array $validate = null,
    ): static {
        $this->attributes[$name] = [$inputName, $validate];
        return $this;
    }
    
    /**
     * Returns the attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Returns true if attribute exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
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