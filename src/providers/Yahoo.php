<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\yahoo\provider\Yahoo as YahooProvider;
use verbb\auth\models\Token;

class Yahoo extends YahooProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://social.yahooapis.com/v1/';
    }
}