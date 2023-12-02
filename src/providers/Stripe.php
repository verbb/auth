<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\stripe\provider\Stripe as StripeProvider;
use verbb\auth\models\Token;

class Stripe extends StripeProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.stripe.com/v1/';
    }
}