<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\snapchat\provider\Snapchat as SnapchatProvider;
use verbb\auth\models\Token;

class Snapchat extends SnapchatProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.snapchat.com/v1/';
    }

    public function getApiRequestQueryParams(?Token $token): array
    {
        return [
            'access_token' => (string)$token->getToken(),
        ];
    }
}