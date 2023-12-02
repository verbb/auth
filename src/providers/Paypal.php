<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\paypal\provider\Paypal as PaypalProvider;
use verbb\auth\models\Token;

class Paypal extends PaypalProvider
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