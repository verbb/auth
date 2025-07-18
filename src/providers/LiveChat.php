<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\livechat\provider\LiveChat as LiveChatProvider;
use verbb\auth\models\Token;

class LiveChat extends LiveChatProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.livechatinc.com/';
    }
}