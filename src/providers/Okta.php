<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\okta\provider\Okta as OktaProvider;
use verbb\auth\models\Token;

class Okta extends OktaProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return $this->getBaseApiUrl();
    }
}