<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\instagram\provider\Instagram as InstagramProvider;
use verbb\auth\models\Token;

class Instagram extends InstagramProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'https://graph.instagram.com';
    }

    public function getApiRequestQueryParams(Token $token): array
    {
        return [
            'access_token' => (string)$token->getToken(),
        ];
    }
}