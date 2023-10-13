<?php

namespace verbb\auth\clients\google\exception;

use Exception;

/**
 * Exception thrown if the Google Provider is configured with a hosted domain that the user doesn't belong to
 */
class HostedDomainException extends Exception
{
    /**
     * @param $configuredDomain
     *
     * @return static
     */
    public static function notMatchingDomain($configuredDomain): self
    {
        return new static("User is not part of domain '$configuredDomain'");
    }
}
