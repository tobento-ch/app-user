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
 * TokenTransportInterface
 */
interface TokenTransportInterface
{
    /**
     * Returns token transport name.
     *
     * @return string
     */
    public function name(): string;
    
    /**
     * Fetch token id from incoming request.
     *
     * @param ServerRequestInterface $request
     * @return null|string
     */
    public function fetchTokenId(ServerRequestInterface $request): null|string;

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
    ): ResponseInterface;

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
    ): ResponseInterface;
}