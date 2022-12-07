<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\sugarcrm\provider\Sugarcrm as SugarcrmProvider;

class Sugarcrm extends SugarcrmProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return $this->url . '/rest/v11/';
    }
}