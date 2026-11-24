<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\unsplash\provider\Unsplash as UnsplashProvider;
use verbb\auth\models\Token;

class Unsplash extends UnsplashProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.unsplash.com/';
    }
}
