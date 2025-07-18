<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\suitecrm\provider\SuiteCrm as SuiteCrmProvider;
use verbb\auth\models\Token;

class SuiteCrm extends SuiteCrmProvider
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