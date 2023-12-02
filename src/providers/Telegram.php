<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\telegram\provider\Telegram as TelegramProvider;
use verbb\auth\models\Token;

class Telegram extends TelegramProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.telegram.org/';
    }
}