<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\buddy\provider\Buddy as BuddyProvider;
use verbb\auth\models\Token;

class Buddy extends BuddyProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return $this->baseUrl;
    }
}