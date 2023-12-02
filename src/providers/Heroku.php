<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\heroku\provider\Heroku as HerokuProvider;
use verbb\auth\models\Token;

class Heroku extends HerokuProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.heroku.com';
    }
}