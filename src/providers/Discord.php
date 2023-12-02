<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\discord\provider\Discord as DiscordProvider;
use verbb\auth\models\Token;

class Discord extends DiscordProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://discordapp.com/api/';
    }
}