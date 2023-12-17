<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\fedex\provider\Fedex as FedexProvider;

class Fedex extends FedexProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://apis.fedex.com/';
    }

    public function getGrant(): string
    {
        return 'client_credentials';
    }
}