<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\spotify\provider\Spotify as SpotifyProvider;
use verbb\auth\models\Token;

class Spotify extends SpotifyProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.spotify.com/v1/';
    }
}