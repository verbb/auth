<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\github\provider\GithubNew as GithubNewProvider;
use verbb\auth\models\Token;

class GithubNew extends GithubNewProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.github.com/';
    }
}