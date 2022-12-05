<?php

namespace verbb\auth\providers\rdio\provider;

use GuzzleHttp\Psr7\Uri;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Rdio extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl()
    {
        return 'https://www.rdio.com/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://services.rdio.com/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://www.yammer.com/api/v1/users/current.json';
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            $msg = $data['error_description'] ?: (string)$response->getReasonPhrase();
            throw new IdentityProviderException(
                $msg,
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new RdioResourceOwner($response);
    }

    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $url = $this->getResourceOwnerDetailsUrl($token);

        $request = $this->getAuthenticatedRequest(self::METHOD_POST, $url, $token);

        return $this->getResponse($request);
    }
}
