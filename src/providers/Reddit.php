<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\reddit\provider\Reddit as RedditProvider;
use verbb\auth\models\Token;

class Reddit extends RedditProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://oauth.reddit.com/api/v1/';
    }
}