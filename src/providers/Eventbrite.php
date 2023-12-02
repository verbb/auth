<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\eventbrite\provider\Eventbrite as EventbriteProvider;
use verbb\auth\models\Token;

class Eventbrite extends EventbriteProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://www.eventbriteapi.com/v3';
    }
}