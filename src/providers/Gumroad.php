<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\gumroad\provider\Gumroad as GumroadProvider;
use verbb\auth\models\Token;

class Gumroad extends GumroadProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->apiDomain . '/api/v2';
    }
}