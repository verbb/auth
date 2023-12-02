<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\rdio\provider\Rdio as RdioProvider;
use verbb\auth\models\Token;

class Rdio extends RdioProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://www.yammer.com/api/v1/';
    }
}