<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\front\provider\Front as FrontProvider;
use verbb\auth\models\Token;

class Front extends FrontProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api2.frontapp.com/';
    }
}