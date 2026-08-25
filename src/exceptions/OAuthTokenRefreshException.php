<?php
namespace verbb\auth\exceptions;

use Exception;
use Throwable;

/**
 * Thrown when an OAuth2 refresh token is permanently unusable (e.g. Google `invalid_grant`).
 *
 * Callers should treat this as “reconnect required” rather than retrying the API request.
 */
class OAuthTokenRefreshException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
