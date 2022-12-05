<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\instagram\provider\Instagram as InstagramProvider;
use verbb\auth\models\Token;

use League\OAuth2\Client\Token\AccessTokenInterface;

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

    public function getAccessToken($grant = 'authorization_code', array $params = []): AccessTokenInterface
    {
        // Facebook doesn't use standard OAuth refresh tokens.
        // Instead it has a "token exchange" system. You exchange the token prior to
        // expiry, to push back expiry. You start with a short-lived token and each
        // exchange gives you a long-lived one (90 days).

        // We act a bit more opinionated by always wanting a long-lived access token.
        if ($grant = 'authorization_code') {
            // Get a short-lived token
            $accessToken = parent::getAccessToken($grant, $params);

            // With the short-lived token, generate a long-lived one.
            return $this->getLongLivedAccessToken((string)$accessToken);
        }

        return parent::getAccessToken($grant, $params);
    }
}