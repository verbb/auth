<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\mastodon\provider\Mastodon as MastodonProvider;
use verbb\auth\models\Token;

class Mastodon extends MastodonProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->instance . '/api/v1/';
    }
}