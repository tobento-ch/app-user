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

use DateTimeInterface;
use DateTimeImmutable;

/**
 * Token
 */
final class Token implements TokenInterface
{
    /**
     * Create a new Token.
     *
     * @param string $id
     * @param array $payload
     * @param string $authenticatedVia The name of which the user was authenticated via loginlink e.g.
     * @param string $authenticatedBy The name of which the user was authenticated by (authenticator class name).
     * @param string $issuedBy The name the token was issued by (storage name).
     * @param DateTimeInterface $issuedAt
     * @param null|DateTimeInterface $expiresAt
     */
    public function __construct(
        protected string $id,
        protected array $payload,
        protected string $authenticatedVia,
        protected null|string $authenticatedBy,
        protected string $issuedBy,
        protected DateTimeInterface $issuedAt,
        protected null|DateTimeInterface $expiresAt = null,
    ) {}
    
    /**
     * Returns the token id.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }
    
    /**
     * Returns a new instance with the specified id.
     *
     * @param string $id
     * @return static
     */
    public function withId(string $id): static
    {
        $new = clone $this;
        $new->id = $id;
        return $new;
    }

    /**
     * Returns the payload.
     *
     * @return array
     */
    public function payload(): array
    {
        return $this->payload;
    }
    
    /**
     * Returns the name of which the user was authenticated via loginlink e.g.
     *
     * @return string
     */
    public function authenticatedVia(): string
    {
        return $this->authenticatedVia;
    }
    
    /**
     * Returns the name of which the user was authenticated by (authenticator class name).
     *
     * @return null|string
     */
    public function authenticatedBy(): null|string
    {
        return $this->authenticatedBy;
    }

    /**
     * Returns the name the token was issued by (storage name).
     *
     * @return string
     */
    public function issuedBy(): string
    {
        return $this->issuedBy;
    }
    
    /**
     * Returns the point in time the token has been issued.
     *
     * @return DateTimeInterface
     */
    public function issuedAt(): DateTimeInterface
    {
        return $this->issuedAt;
    }
    
    /**
     * Returns the point in time after which the token MUST be considered expired.
     *
     * @return null|DateTimeInterface
     */
    public function expiresAt(): null|DateTimeInterface
    {
        return $this->expiresAt;
    }
    
    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     *
     * @return array
     */    
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id(),
            'payload' => $this->payload(),
            'authenticatedVia' => $this->authenticatedVia(),
            'authenticatedBy' => $this->authenticatedBy(),
            'issuedBy' => $this->issuedBy(),
            'issuedAt' => $this->issuedAt()->getTimestamp(),
            'expiresAt' => $this->expiresAt()?->getTimestamp(),
        ];
    }
    
    /**
     * Returns a new instance created from the specified array data.
     *
     * @param array $data
     * @return static
     * @throws \Throwable
     * @psalm-suppress TooFewArguments
     */
    public static function fromArray(array $data): static
    {
        $data['issuedAt'] = (new DateTimeImmutable())->setTimestamp($data['issuedAt']);
        
        if ($data['expiresAt'] !== null) {
            $data['expiresAt'] = (new DateTimeImmutable())->setTimestamp($data['expiresAt']);
        }
        
        return new static(...$data);
    }
    
    /**
     * Returns a new instance created from the specified JSON string.
     *
     * @param string $json
     * @return static
     * @throws \Throwable
     */
    public static function fromJsonString(string $json): static
    {
        return static::fromArray(json_decode($json, true));
    }
}