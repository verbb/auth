<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\buffer\provider\Buffer as BufferProvider;
use verbb\auth\models\Token;

class Buffer extends BufferProvider
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