<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\mollie\provider\Mollie as MollieProvider;
use verbb\auth\models\Token;

class Mollie extends MollieProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return static::MOLLIE_API_URL . '/v2/';
    }
}