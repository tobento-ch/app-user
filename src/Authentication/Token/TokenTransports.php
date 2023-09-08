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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tobento\App\User\Exception\TokenTransportException;
use Throwable;

/**
 * TokenTransports
 */
class TokenTransports implements TokenTransportsInterface, TokenTransportInterface
{
    /**
     * @var array<string, callable|TokenTransportInterface>
     */
    protected array $transports = [];
    
    /**
     * @var null|TokenTransportInterface
     */
    protected null|TokenTransportInterface $tokenTransport = null;
    
    /**
     * Create a new TokenTransports.
     *
     * @param TokenTransportInterface ...$transports
     */
    public function __construct(
        TokenTransportInterface ...$transports,
    ) {
        foreach($transports as $transport) {
            $this->add($transport);
        }
    }
    
    /**
     * Add a token transport.
     *
     * @param TokenTransportInterface $transport
     * @return static $this
     */
    public function add(TokenTransportInterface $transport): static
    {
        $this->transports[$transport->name()] = $transport;
        return $this;
    }
    
    /**
     * Register a token transport.
     *
     * @param string $name The token transport name.
     * @param callable $transport
     * @return static $this
     */    
    public function register(string $name, callable $transport): static
    {
        $this->transports[$name] = $transport;
        return $this;
    }
    
    /**
     * Returns true if the token transport exists, otherwise false.
     *
     * @param string $name The token transport name.
     * @return bool
     */    
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->transports);
    }
    
    /**
     * Returns the token transport by name.
     *
     * @param string $name The token transport name
     * @return TokenTransportInterface
     * @throws TokenTransportException
     */    
    public function get(string $name): TokenTransportInterface
    {
        if (!$this->has($name)) {
            throw new TokenTransportException(sprintf('Transport [%s] not found!', $name));
        }
        
        if (! $this->transports[$name] instanceof TokenTransportInterface) {
            try {
                $this->transports[$name] = $this->createTransport($name, $this->transports[$name]);
            } catch(Throwable $e) {
                throw new TokenTransportException($e->getMessage(), (int)$e->getCode(), $e);
            }
        }
        
        return $this->transports[$name];
    }
    
    /**
     * Returns a new instance with the specified transports.
     *
     * @param array $names The token transport names
     * @return TokenTransportInterface
     * @throws TokenTransportException
     */    
    public function only(array $names): TokenTransportInterface
    {
        $transports = [];
        
        foreach($names as $name) {
            if (isset($this->transports[$name])) {
                $transports[$name] = $this->transports[$name];
            }
        }
            
        $new = clone $this;
        $new->transports = $transports;
        return $new;
    }
    
    /**
     * Returns token transport name.
     *
     * @return string
     */
    public function name(): string
    {
        if (!is_null($this->tokenTransport)) {
            return $this->tokenTransport->name();
        }
        
        return 'transports';
    }
    
    /**
     * Fetch token id from incoming request.
     *
     * @param ServerRequestInterface $request
     * @return null|string
     */
    public function fetchTokenId(ServerRequestInterface $request): null|string
    {
        foreach(array_keys($this->transports) as $transportName) {
            
            $transport = $this->get($transportName);
            
            if (!is_null($tokenId = $transport->fetchTokenId($request))) {
                $this->tokenTransport = $transport;
                return $tokenId;
            }
        }
        
        return null;
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
        
        if (!is_null($this->tokenTransport)) {
            return $this->tokenTransport->commitToken($token, $request, $response);
        }
        
        foreach($this->transports as $transport) {
            $response = $transport->commitToken($token, $request, $response);
        }
        
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
        
        if (!is_null($this->tokenTransport)) {
            return $this->tokenTransport->removeToken($token, $request, $response);
        }
        
        foreach($this->transports as $transport) {
            $response = $transport->removeToken($token, $request, $response);
        }
        
        return $response;
    }
    
    /**
     * Create a new token transport.
     *
     * @param string $name
     * @param callable $factory
     * @return TokenTransportInterface
     */
    protected function createTransport(string $name, callable $factory): TokenTransportInterface
    {
        return call_user_func_array($factory, [$name]);
    }
}