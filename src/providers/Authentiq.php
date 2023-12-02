<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\authentiq\provider\Authentiq as AuthentiqProvider;
use verbb\auth\models\Token;

class Authentiq extends AuthentiqProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://connect.authentiq.io/';
    }
}