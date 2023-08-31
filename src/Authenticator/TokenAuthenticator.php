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
use Tobento\App\User\Authentication\Token\TokenInterface;
use Tobento\App\User\Exception\InvalidTokenException;
use Tobento\App\User\Exception\AuthenticationException;

/**
 * TokenAuthenticator
 */
class TokenAuthenticator implements TokenAuthenticatorInterface
{
    /**
     * Create a new TokenAuthenticator.
     *
     * @param UserRepositoryInterface $userRepository
     * @param null|TokenVerifierInterface $tokenVerifier
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected null|TokenVerifierInterface $tokenVerifier = null,
    ) {}
    
    /**
     * Authenticate token.
     *
     * @param TokenInterface $token
     * @return UserInterface
     * @throws AuthenticationException If authentication fails.
     */
    public function authenticate(TokenInterface $token): UserInterface
    {
        // You may use different user repo depending on authenticator:
        // $authenticator = $token->authenticatedBy();
        // Some logic:
        
        if (!isset($token->payload()['userId'])) {
            throw new InvalidTokenException(message: 'No user id provided', token: $token);
        }
        
        // fetch user:
        $user = $this->userRepository->findById($token->payload()['userId']);
        
        if (is_null($user)) {
            throw new AuthenticationException('User not found');
        }
        
        // verify token:
        if (!is_null($this->tokenVerifier)) {
            $this->tokenVerifier->verify($token, $user);
        }
        
        return $user;
    }
}