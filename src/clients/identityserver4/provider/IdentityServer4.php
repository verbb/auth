<?php
namespace verbb\auth\clients\identityserver4\provider;

use verbb\auth\clients\auth0\provider\Auth0;

class IdentityServer4 extends Auth0
{
    public function getBaseAccessTokenUrl(array $params = [])
    {
        return $this->baseUrl() . '/token';
    }
}