<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\marketo\provider\Marketo as MarketoProvider;
use verbb\auth\models\Token;

class Marketo extends MarketoProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->getApiUrl();
    }
}