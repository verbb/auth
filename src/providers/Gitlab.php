<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\gitlab\provider\Gitlab as GitlabProvider;
use verbb\auth\models\Token;

class Gitlab extends GitlabProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://gitlab.com/api/v3/';
    }
}