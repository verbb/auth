<?php
namespace verbb\auth\providers\auth0\provider\exception;

use RuntimeException;

class InvalidRegionException extends RuntimeException
{
    protected $message = 'Invalid region provided';
}
