<?php

namespace verbb\auth\clients\vend\grant;

use League\OAuth2\Client\Grant\AbstractGrant;

class VendExchangeToken extends AbstractGrant
{
    public function __toString()
    {
        return 'vend_exchange_token';
    }

    protected function getRequiredRequestParameters(): array
    {
        return [
            'vend_exchange_token',
        ];
    }

    protected function getName(): string
    {
        return 'vend_exchange_token';
    }
}
