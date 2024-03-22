<?php
namespace verbb\auth\clients\identifyserver4\provider;

use verbb\auth\clients\auth0\provider\Auth0;

class IdentifyServer4 extends Auth0
{
    public function getBaseAccessTokenUrl(array $params = [])
    {
        return $this->baseUrl() . '/token';
    }
}