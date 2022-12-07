<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\constantcontact\provider\ConstantContact as ConstantContactProvider;

class ConstantContact extends ConstantContactProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://api.cc.email/v3/';
    }
}