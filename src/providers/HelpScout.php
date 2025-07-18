<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\helpscout\provider\HelpScout as HelpScoutProvider;
use verbb\auth\models\Token;

class HelpScout extends HelpScoutProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.helpscout.net/v2/';
    }
}