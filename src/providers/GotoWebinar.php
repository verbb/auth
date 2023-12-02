<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\gotowebinar\provider\GotoWebinar as GotoWebinarProvider;
use verbb\auth\models\Token;

class GotoWebinar extends GotoWebinarProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->domain . '/admin/rest/v1';
    }
}