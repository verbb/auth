<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\infusionsoft\provider\Infusionsoft as InfusionsoftProvider;
use verbb\auth\models\Token;

class Infusionsoft extends InfusionsoftProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.infusionsoft.com/crm/rest/v1';
    }
}