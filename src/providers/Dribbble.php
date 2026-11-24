<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\dribbble\provider\Dribbble as DribbbleProvider;
use verbb\auth\models\Token;

class Dribbble extends DribbbleProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.dribbble.com/v2/';
    }
}
