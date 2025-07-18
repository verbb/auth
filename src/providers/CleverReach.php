<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\cleverreach\provider\CleverReach as CleverReachProvider;
use verbb\auth\models\Token;

class CleverReach extends CleverReachProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://rest.cleverreach.com/v3/';
    }
}