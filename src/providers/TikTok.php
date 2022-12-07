<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\tiktok\provider\TikTokAuthProvider as TikTokProvider;

class TikTok extends TikTokProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://open-api.tiktok.com/';
    }
}