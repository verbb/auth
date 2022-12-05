<?php

namespace verbb\auth\clients\instagram\grant;

use League\OAuth2\Client\Grant\AbstractGrant;

class IgRefreshToken extends AbstractGrant
{
    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return 'ig_refresh_token';
    }

    /**
     * @inheritdoc
     */
    protected function getRequiredRequestParameters()
    {
        return [
            'access_token',
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getName()
    {
        return 'ig_refresh_token';
    }
}
