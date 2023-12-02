<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\twitch\provider\Twitch as TwitchProvider;
use verbb\auth\models\Token;

class Twitch extends TwitchProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.twitch.tv/helix/';
    }
}