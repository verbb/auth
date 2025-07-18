<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\intercom\provider\Intercom as IntercomProvider;
use verbb\auth\models\Token;

class Intercom extends IntercomProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.intercom.io/';
    }
}