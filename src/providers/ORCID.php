<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\orcid\provider\ORCID as ORCIDProvider;
use verbb\auth\models\Token;

class ORCID extends ORCIDProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://pub.orcid.org/v2.1/';
    }
}