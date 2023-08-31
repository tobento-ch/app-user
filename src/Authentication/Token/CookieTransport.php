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

namespace Tobento\App\User\Authentication\Token;

use Tobento\Service\Cookie\CookieValuesInterface;
use Tobento\Service\Cookie\CookiesInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Clock\ClockInterface;

/**
 * CookieTransport
 */
final class CookieTransport implements TokenTransportInterface
{
    /**
     * Create a new CookieTransport.
     *
     * @param ClockInterface $clock
     * @param string $cookieName
     * @param null|string $path
     * @param null|string $domain
     * @param null|bool $secure
     * @param bool $httpOnly
     * @param null|string $sameSite
     */
    public function __construct(
        private ClockInterface $clock,
        private string $cookieName = 'token',
        private null|string $path = null,
        private null|string $domain = null,
        private null|bool $secure = null,
        private bool $httpOnly = true,
        private null|string $sameSite = null,
    ) {}

    /**
     * Returns the cookie name.
     *
     * @return string
     */
    public function cookieName(): string
    {
        return $this->cookieName;
    }
    
    /**
     * Returns token transport name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'cookie';
    }
    
    /**
     * Fetch token id from incoming request.
     *
     * @param ServerRequestInterface $request
     * @return null|string
     */
    public function fetchTokenId(ServerRequestInterface $request): null|string
    {
        return $request->getAttribute(CookieValuesInterface::class)?->get($this->cookieName);
    }

    /**
     * Commit token to the outgoing response.
     *
     * @param TokenInterface $token
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function commitToken(
        TokenInterface $token,
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): ResponseInterface {

        $cookies = $request->getAttribute(CookiesInterface::class);
        
        if (is_null($cookies)) {
            return $response;
        }
        
        $lifetime = null;
        
        if (!is_null($token->expiresAt())) {
            $lifetime = max($token->expiresAt()->getTimestamp() - $this->clock->now()->getTimestamp(), 0);
        }

        $cookies->add(
            name: $this->cookieName,
            value: $token->id(),

            // The duration in seconds until the cookie will expire.
            lifetime: $lifetime,
            
            // if null (default) it uses default value from factory.
            path: $this->path, // null|string

            // if null (default) it uses default value from factory.
            domain: $this->domain, // null|string

            // if null (default) it uses default value from factory.
            secure: $this->secure, // null|bool

            httpOnly: $this->httpOnly, // default true if not set

            // if null (default) it uses default value from factory.
            sameSite: $this->sameSite, // string
        );
        
        return $response;
    }

    /**
     * Remove token from the outgoing response.
     *
     * @param TokenInterface $token
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function removeToken(
        TokenInterface $token,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        
        $cookies = $request->getAttribute(CookiesInterface::class);
        
        if (is_null($cookies)) {
            return $response;
        }
        
        $cookies->clear(name: $this->cookieName);
        return $response;
    }
}