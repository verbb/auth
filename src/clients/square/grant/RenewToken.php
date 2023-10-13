<?php

namespace verbb\auth\clients\square\grant;

use League\OAuth2\Client\Grant\AbstractGrant;

class RenewToken extends AbstractGrant
{
    protected function getName(): string
    {
        return 'renew_token';
    }

    protected function getRequiredRequestParameters(): array
    {
        return [
            'access_token',
        ];
    }
}
