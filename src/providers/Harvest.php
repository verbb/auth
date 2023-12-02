<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\harvest\provider\Harvest as HarvestProvider;
use verbb\auth\models\Token;

class Harvest extends HarvestProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->apiDomain;
    }
}