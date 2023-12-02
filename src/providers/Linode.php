<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\linode\provider\Linode as LinodeProvider;
use verbb\auth\models\Token;

class Linode extends LinodeProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.linode.com/v4/';
    }
}