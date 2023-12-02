<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\constantcontact\provider\ConstantContact as ConstantContactProvider;
use verbb\auth\models\Token;

class ConstantContact extends ConstantContactProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.cc.email/v3/';
    }
}