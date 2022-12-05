<?php
namespace verbb\auth\clients\auth0\provider\exception;

use RuntimeException;

class InvalidRegionException extends RuntimeException
{
    protected $message = 'Invalid region provided';
}
