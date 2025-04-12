<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\gitlab\provider\GitlabNew as GitlabNewProvider;
use verbb\auth\models\Token;

class GitlabNew extends GitlabNewProvider
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