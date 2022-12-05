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

    public function getBaseApiUrl(): ?string
    {
        return 'https://accounts.zoho.com/oauth/';
    }
}