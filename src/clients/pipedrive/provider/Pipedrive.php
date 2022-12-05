<?php

namespace verbb\auth\clients\pipedrive\provider;

use verbb\auth\clients\pipedrive\PipedriveResourceOwner;
use verbb\auth\clients\pipedrive\provider\exception\PipedriveIdentityProviderException;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Pipedrive extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl()
    {
        return 'https://oauth.pipedrive.com/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://oauth.pipedrive.com/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api-proxy.pipedrive.com/users/me';
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new PipedriveIdentityProviderException(
                $data['error_description'] ?: $response->getReasonPhrase(),
                $data['status_code'] ?: $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new PipedriveResourceOwner($response);
    }
}