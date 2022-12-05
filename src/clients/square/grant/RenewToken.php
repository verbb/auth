<?php

namespace verbb\auth\clients\square\grant;

use League\OAuth2\Client\Grant\AbstractGrant;

class RenewToken extends AbstractGrant
{
    protected function getName()
    {
        return 'renew_token';
    }

    protected function getRequiredRequestParameters()
    {
        return [
            'access_token',
        ];
    }
}
