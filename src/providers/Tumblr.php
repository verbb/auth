<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\tumblr\provider\Tumblr as TumblrProvider;
use verbb\auth\models\Token;

class Tumblr extends TumblrProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.tumblr.com/v2/';
    }
}