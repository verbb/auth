<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\linkedin\provider\LinkedIn as LinkedInProvider;

class LinkedIn extends LinkedInProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://api.linkedin.com/v2/';
    }
}