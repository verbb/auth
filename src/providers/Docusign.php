<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\docusign\provider\Docusign as DocusignProvider;
use verbb\auth\models\Token;

class Docusign extends DocusignProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->sandbox ? self::URL_ROOT_SANDBOX : self::URL_ROOT;
    }
}