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

use JsonSerializable;
use DateTimeInterface;

/**
 * The token.
 */
interface TokenInterface extends JsonSerializable
{
    /**
     * Returns the token id.
     *
     * @return string
     */
    public function id(): string;
    
    /**
     * Returns the payload.
     *
     * @return array
     */
    public function payload(): array;
    
    /**
     * Returns the name of which the user was authenticated via loginlink e.g.
     *
     * @return string
     */
    public function authenticatedVia(): string;

    /**
     * Returns the name of which the user was authenticated by (authenticator class name).
     *
     * @return null|string
     */
    public function authenticatedBy(): null|string;
    
    /**
     * Returns the name the token was issued by (storage name).
     *
     * @return string
     */
    public function issuedBy(): string;
    
    /**
     * Returns the point in time the token has been issued.
     *
     * @return DateTimeInterface
     */
    public function issuedAt(): DateTimeInterface;
    
    /**
     * Returns the point in time after which the token MUST be considered expired.
     *
     * @return null|DateTimeInterface
     */
    public function expiresAt(): null|DateTimeInterface;
}