<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\bitbucket\provider\Bitbucket as BitbucketProvider;
use verbb\auth\models\Token;

class Bitbucket extends BitbucketProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.bitbucket.org/2.0/';
    }
}