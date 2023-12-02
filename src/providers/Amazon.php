<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\amazon\provider\Amazon as AmazonProvider;
use verbb\auth\models\Token;

class Amazon extends AmazonProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.amazon.com/';
    }
}