<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\weibo\provider\Weibo as WeiboProvider;
use verbb\auth\models\Token;

class Weibo extends WeiboProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->domain . '/2/';
    }
}