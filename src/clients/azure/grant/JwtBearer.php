<?php

namespace verbb\auth\clients\azure\grant;

class JwtBearer extends \League\OAuth2\Client\Grant\AbstractGrant
{
    protected function getName()
    {
        return 'urn:ietf:params:oauth:grant-type:jwt-bearer';
    }

    protected function getRequiredRequestParameters()
    {
        return [
            'requested_token_use',
            'assertion',
        ];
    }
}
