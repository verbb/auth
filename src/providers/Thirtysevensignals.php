<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\thirtysevensignals\provider\Thirtysevensignals as ThirtysevensignalsProvider;
use verbb\auth\models\Token;

class Thirtysevensignals extends ThirtysevensignalsProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://launchpad.37signals.com/';
    }
}