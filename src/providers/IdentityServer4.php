<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\identityserver4\provider\IdentityServer4 as IdentityServer4Provider;

class IdentityServer4 extends IdentityServer4Provider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return $this->baseUrl();
    }
}