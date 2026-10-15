<?php
namespace verbb\auth\clients\constantcontact\provider;

use verbb\auth\helpers\ProviderError;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class ConstantContact extends AbstractProvider
{
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://authz.constantcontact.com/oauth2/default/v1/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://authz.constantcontact.com/oauth2/default/v1/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.cc.email/v3/account/summary';
    }

    public function getDefaultScopes(): array
    {
        return [];
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function getAuthorizationHeaders($token = null): array
    {
        return ['Authorization' => 'Bearer ' . $token->getToken()];
    }

    public function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400 || !empty($data['error']) || !empty($data['errors'])) {
            throw new IdentityProviderException(ProviderError::message($data, $response->getReasonPhrase()), 0, $data);
        }

        return $data;
    }


    protected function createResourceOwner(array $response, AccessToken $token): ConstantContactAccount
    {
        return new ConstantContactAccount($response);
    }
}
