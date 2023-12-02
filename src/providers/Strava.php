<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\strava\provider\Strava as StravaProvider;
use verbb\auth\models\Token;

class Strava extends StravaProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://www.strava.com/api/v3/';
    }
}