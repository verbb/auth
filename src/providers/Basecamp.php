<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\basecamp\provider\Basecamp as BasecampProvider;
use verbb\auth\models\Token;

class Basecamp extends BasecampProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->getHost();
    }
}