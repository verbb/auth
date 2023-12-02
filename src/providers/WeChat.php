<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\wechat\provider\WebProvider as WeChatProvider;
use verbb\auth\models\Token;

class WeChat extends WeChatProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.weixin.qq.com/sns';
    }
}