<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\microsoftentra\provider\MicrosoftEntra as MicrosoftEntraProvider;
use verbb\auth\models\Token;

class MicrosoftEntra extends MicrosoftEntraProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://graph.microsoft.com/v1.0/';
    }
}
