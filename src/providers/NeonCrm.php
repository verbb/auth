<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\neoncrm\provider\NeonCrm as NeonCrmProvider;

class NeonCrm extends NeonCrmProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://api.neoncrm.com/v2/';
    }
}