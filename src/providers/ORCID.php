<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\orcid\provider\ORCID as ORCIDProvider;

class ORCID extends ORCIDProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://pub.orcid.org/v2.1/';
    }
}