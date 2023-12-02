<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\etsy\provider\Etsy as EtsyProvider;
use verbb\auth\models\Token;

class Etsy extends EtsyProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->baseApiUrl . '/application/';
    }
}