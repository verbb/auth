<?php

namespace verbb\auth\clients\okta\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Okta extends AbstractProvider
{
    use BearerAuthorizationTrait;
    
    protected string $issuer = '';
    protected string $apiVersion = 'v1';
    
    
    public function getBaseApiUrl(): string
    {
        return $this->issuer . '/' . $this->apiVersion;
    }
    
    /**
     * Get authorization url to begin OAuth flow
     *
     * @link https://developer.okta.com/docs/reference/api/oidc/#authorize
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->getBaseApiUrl().'/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @link https://developer.okta.com/docs/reference/api/oidc/#token
     * @param  array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getBaseApiUrl().'/token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @link https://developer.okta.com/docs/reference/api/oidc/#userinfo
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getBaseApiUrl().'/userinfo';
    }

    protected function getDefaultScopes(): array
    {
        return [
            'openid',
            'email',
            'profile'
        ];
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        // @codeCoverageIgnoreStart
        if (empty($data['error'])) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $code = $response->getStatusCode();
        $error = $data['error'];

        if (is_array($error)) {
            $code = $error['code'];
            $error = $error['message'];
        }

        throw new IdentityProviderException($error, $code, $data);
    }

    protected function createResourceOwner(array $response, AccessToken $token): OktaUser
    {
        return new OktaUser($response);
    }
}
