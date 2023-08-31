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

/**
 * HeaderTransport
 */
final class HeaderTransport implements TokenTransportInterface
{
    /**
     * Create a new HeaderTransport.
     *
     * @param string $name The transport name
     * @param string $headerName
     */
    public function __construct(
        private string $name = 'header',
        private string $headerName = 'X-Auth-Token',
    ) {}
    
    /**
     * Returns token transport name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * Fetch token id from incoming request.
     *
     * @param ServerRequestInterface $request
     * @return null|string
     */
    public function fetchTokenId(ServerRequestInterface $request): null|string
    {
        if ($request->hasHeader($this->headerName)) {
            return $request->getHeaderLine($this->headerName);
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
        if (
            $request->hasHeader($this->headerName)
            && $request->getHeaderLine($this->headerName) === $token->id()
        ) {
            return $response;
        }

        return $response->withAddedHeader($this->headerName, $token->id());
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
        return $response;
    }
}