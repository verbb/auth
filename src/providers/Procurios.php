<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\procurios\provider\Procurios as ProcuriosProvider;
use verbb\auth\models\Token;

class Procurios extends ProcuriosProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://identity.procurios.com/api/';
    }
}