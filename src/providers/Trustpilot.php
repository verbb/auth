<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\trustpilot\provider\Trustpilot as TrustpilotProvider;

class Trustpilot extends TrustpilotProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://api.trustpilot.com/v1/';
    }
}