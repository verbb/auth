<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\onecrm\provider\OneCrm as OneCrmProvider;
use verbb\auth\models\Token;

class OneCrm extends OneCrmProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->getApiUrl();
    }
}