<?php

namespace verbb\auth\clients\etsy\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\etsy\exception\EtsyIdentityProviderException;

class Etsy extends AbstractProvider
{

    use BearerAuthorizationTrait;

    protected string $baseApiUrl = 'https://openapi.etsy.com/v3';

    public function getBaseAuthorizationUrl() : string
    {
        return 'https://www.etsy.com/oauth/connect';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->baseApiUrl . '/public/oauth/token?' . $this->buildQueryString($params);
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token) : string
    {
        // we need to get the userId from the access token
        $tokenData = explode('.', $token->getToken());
        return $this->baseApiUrl . '/application/users/' . $tokenData[0];
    }

    protected function getDefaultScopes() : array
    {
        return ['email_r'];
    }

    protected function checkResponse(ResponseInterface $response, $data) : void
    {
        if ($response->getStatusCode() >= 400) {
            throw EtsyIdentityProviderException::clientException($response, $data);
        }

        if (isset($data['error'])) {
            throw EtsyIdentityProviderException::oauthException($response, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new EtsyResourceOwner($response, $token);
    }

    public function getDefaultHeaders() : array
    {
        return [
            'x-api-key' => $this->clientId
        ];
    }

    protected function getScopeSeparator() : string
    {
        return ' ';
    }

    public function getPreChallenge() : string
    {
        return substr(
            strtr(
                base64_encode(random_bytes(64)),
                '+/',
                '-_'
            ),
            0,
            64
        );
    }

    public function getPKCE($preChallenge) : string
    {
        return trim(
            strtr(
                base64_encode(hash('sha256', $preChallenge, true)),
                '+/',
                '-_'
            ),
            '='
        );
    }
}
