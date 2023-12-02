<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\hubspot\provider\HubSpot as HubSpotProvider;
use verbb\auth\models\Token;

class HubSpot extends HubSpotProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->baseApiUrl;
    }
}