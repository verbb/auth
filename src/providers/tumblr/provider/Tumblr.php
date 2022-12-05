<?php

namespace verbb\auth\providers\tumblr\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Tumblr extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl()
    {
        return 'https://www.tumblr.com/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://www.tumblr.com/oauth2/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.tumblr.com/v2/user/info';
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException($response->getReasonPhrase(), $response->getStatusCode(), $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new TumblrUser($response);
    }
}
