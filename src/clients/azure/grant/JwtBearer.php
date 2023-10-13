<?php

namespace verbb\auth\clients\azure\grant;

use League\OAuth2\Client\Grant\AbstractGrant;

class JwtBearer extends AbstractGrant
{
    protected function getName(): string
    {
        return 'urn:ietf:params:oauth:grant-type:jwt-bearer';
    }

    protected function getRequiredRequestParameters(): array
    {
        return [
            'requested_token_use',
            'assertion',
        ];
    }
}
