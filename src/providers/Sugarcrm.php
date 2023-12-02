<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\sugarcrm\provider\Sugarcrm as SugarcrmProvider;
use verbb\auth\models\Token;

class Sugarcrm extends SugarcrmProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->url . '/rest/v11/';
    }
}