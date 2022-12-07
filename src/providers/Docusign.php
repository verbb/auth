<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\docusign\provider\Docusign as DocusignProvider;

class Docusign extends DocusignProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return $this->sandbox ? self::URL_ROOT_SANDBOX : self::URL_ROOT;
    }
}