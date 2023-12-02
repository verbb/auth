<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\apple\provider\Apple as AppleProvider;
use verbb\auth\models\Token;

class Apple extends AppleProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://appleid.apple.com/auth/';
    }
}