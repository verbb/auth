<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\pipedrive\provider\Pipedrive as PipedriveProvider;

class Pipedrive extends PipedriveProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://api-proxy.pipedrive.com/';
    }
}