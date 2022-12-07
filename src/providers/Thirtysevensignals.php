<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\thirtysevensignals\provider\Thirtysevensignals as ThirtysevensignalsProvider;

class Thirtysevensignals extends ThirtysevensignalsProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://launchpad.37signals.com/';
    }
}