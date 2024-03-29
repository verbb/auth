<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\bitbucket\provider\Bitbucket as BitbucketProvider;

class Bitbucket extends BitbucketProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://api.bitbucket.org/2.0/';
    }
}