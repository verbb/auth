<?php

namespace verbb\auth\providers\deviantart\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class DeviantArt extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl()
    {
        return 'https://www.deviantart.com/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://www.deviantart.com/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://www.deviantart.com/api/v1/oauth2/user/whoami';
    }

    protected function getDefaultScopes()
    {
        return ['user'];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response->getBody()
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new DeviantArtResourceOwner($response);
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * @inheritdoc
     * @throws IdentityProviderException
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode > 500) {
            throw new IdentityProviderException(
                'The OAuth server returned an unexpected response',
                $statusCode,
                $response->getBody()
            );
        }

        return parent::parseResponse($response);
    }
}
