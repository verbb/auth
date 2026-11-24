<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\aweber\provider\Aweber as AweberProvider;
use verbb\auth\models\Token;

class Aweber extends AweberProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.aweber.com/1.0';
    }
}
