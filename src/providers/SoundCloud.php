<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\soundcloud\provider\SoundCloud as SoundCloudProvider;

class SoundCloud extends SoundCloudProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://api.soundcloud.com/';
    }
}