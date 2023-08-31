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

namespace Tobento\App\User\Exception;

use Tobento\Service\Validation\ValidationInterface;
use Throwable;

/**
 * AuthenticationValidationException
 */
class AuthenticationValidationException extends AuthenticationException
{
    /**
     * Create a new AuthenticationValidationException.
     *
     * @param ValidationInterface $validation
     * @param string $message
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        protected ValidationInterface $validation,
        string $message = 'Validation failed.',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Returns the validation.
     *
     * @return ValidationInterface
     */
    public function validation(): ValidationInterface
    {
        return $this->validation;
    }
}