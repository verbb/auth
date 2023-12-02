<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\microsoft\provider\Microsoft as MicrosoftProvider;
use verbb\auth\models\Token;

class Microsoft extends MicrosoftProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://graph.microsoft.com/v1.0/';
    }

    public function getApiRequestQueryParams(?Token $token): array
    {
        return [
            'access_token' => (string)$token->getToken(),
        ];
    }
}