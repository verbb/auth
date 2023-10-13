<?php

namespace verbb\auth\clients\instagram\grant;

use League\OAuth2\Client\Grant\AbstractGrant;

class IgRefreshToken extends AbstractGrant
{
    public function __toString()
    {
        return 'ig_refresh_token';
    }

    protected function getRequiredRequestParameters(): array
    {
        return [
            'access_token',
        ];
    }

    protected function getName(): string
    {
        return 'ig_refresh_token';
    }
}
