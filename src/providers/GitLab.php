<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\gitlab\provider\GitLab as GitLabProvider;
use verbb\auth\models\Token;

class GitLab extends GitLabProvider
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