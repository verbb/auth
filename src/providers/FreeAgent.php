<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\freeagent\provider\FreeAgent as FreeAgentProvider;

class FreeAgent extends FreeAgentProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://api.freeagent.com/v2/';
    }
}