<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\envato\provider\Envato as EnvatoProvider;
use verbb\auth\models\Token;

class Envato extends EnvatoProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->apiDomain . '/v1/market/private';
    }
}