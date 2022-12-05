<?php
namespace verbb\auth\providers\auth0\provider\exception;

use RuntimeException;

class AccountNotProvidedException extends RuntimeException
{
    protected $message = 'Auth0 account is not provided';
}
