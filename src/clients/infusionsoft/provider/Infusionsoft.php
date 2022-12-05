<?php

namespace verbb\auth\clients\infusionsoft\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Infusionsoft extends AbstractProvider
{
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://signin.infusionsoft.com/app/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://api.infusionsoft.com/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.infusionsoft.com/crm/rest/v1/oauth/connect/userinfo';
    }

    protected function getDefaultScopes(): array
    {
        return ['full'];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            throw new IdentityProviderException(
                $data['message'] ?? $response->getReasonPhrase(),
                $statusCode,
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): InfusionsoftResourceOwner
    {
        return new InfusionsoftResourceOwner($response);
    }

    protected function getAuthorizationHeaders($token = null): array
    {
        if (null === $token) {
            return array_merge(parent::getAuthorizationHeaders($token), [
                'Authorization' => 'Bearer '.base64_encode($this->clientId.':'.$this->clientSecret)
            ]);
        }

        return array_merge(parent::getAuthorizationHeaders($token), [
            'Authorization' => 'Bearer '.$token->getToken()
        ]);
    }
}