<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\zoho\provider\Zoho as ZohoProvider;
use verbb\auth\models\Token;

class Zoho extends ZohoProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        // Use the API domain from the token, or fallback to defualts
        return $token->getToken()->getValues()['api_domain'] ?? $this->getApiUrl();
    }
}