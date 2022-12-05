<?php
namespace verbb\auth\clients\auth0\provider\exception;

use RuntimeException;

class AccountNotProvidedException extends RuntimeException
{
    protected $message = 'Auth0 account is not provided';
}
