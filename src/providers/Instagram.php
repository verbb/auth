<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\instagram\provider\Instagram as InstagramProvider;
use verbb\auth\models\Token;

use League\OAuth2\Client\Token\AccessToken as OAuth2Token;

class Instagram extends InstagramProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://graph.instagram.com';
    }

    public function getApiRequestQueryParams(?Token $token): array
    {
        return [
            'access_token' => (string)$token->getToken(),
        ];
    }

    public function getRefreshToken(OAuth2Token $accessToken): OAuth2Token
    {
        return $this->getRefreshedAccessToken($accessToken);
    }
}