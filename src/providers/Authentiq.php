<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\authentiq\provider\Authentiq as AuthentiqProvider;

class Authentiq extends AuthentiqProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://connect.authentiq.io/';
    }
}