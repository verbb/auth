<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\auth0\provider\Auth0 as Auth0Provider;
use verbb\auth\models\Token;

class Auth0 extends Auth0Provider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->baseUrl();
    }
}