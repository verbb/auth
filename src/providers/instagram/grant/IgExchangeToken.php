<?php

namespace verbb\auth\providers\instagram\grant;

use League\OAuth2\Client\Grant\AbstractGrant;

class IgExchangeToken extends AbstractGrant
{
    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return 'ig_exchange_token';
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
        return 'ig_exchange_token';
    }
}
