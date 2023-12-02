<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\deviantart\provider\DeviantArt as DeviantArtProvider;
use verbb\auth\models\Token;

class DeviantArt extends DeviantArtProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://www.deviantart.com/api/v1/oauth2/';
    }
}