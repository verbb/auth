<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\foursquare\provider\Foursquare as FoursquareProvider;
use verbb\auth\models\Token;

class Foursquare extends FoursquareProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.foursquare.com/v2/';
    }
}