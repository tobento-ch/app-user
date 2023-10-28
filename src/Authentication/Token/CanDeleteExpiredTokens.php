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
 * CanDeleteExpiredTokens
 */
interface CanDeleteExpiredTokens
{
    /**
     * Deletes all expired tokens.
     *
     * @return bool
     *   True if the tokens were successfully deleted. False if there was an error.
     */
    public function deleteExpiredTokens(): bool;
}