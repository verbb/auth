<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\linkedin\provider\LinkedIn as LinkedInProvider;
use verbb\auth\models\Token;

class LinkedIn extends LinkedInProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.linkedin.com/v2/';
    }
}