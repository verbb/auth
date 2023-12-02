<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\deezer\provider\Deezer as DeezerProvider;
use verbb\auth\models\Token;

class Deezer extends DeezerProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.deezer.com/';
    }

    public function getApiRequestQueryParams(?Token $token): array
    {
        return [
            'access_token' => (string)$token->getToken(),
        ];
    }
}