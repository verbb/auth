<?php

namespace verbb\auth\clients\marketo\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Marketo extends AbstractProvider
{
    protected $baseUrl;

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getBaseUrl() . "/identity/oauth/token";
    }

    protected function createAccessToken(array $response, AbstractGrant $grant): \Kristenlk\OAuth2\Client\Token\AccessToken
    {
        return new \Kristenlk\OAuth2\Client\Token\AccessToken($response);
    }

    /**
     * Check a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string $data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }


    public function getBaseAuthorizationUrl() {}
    public function getResourceOwnerDetailsUrl(AccessToken $token) {}
    protected function getDefaultScopes() {}
    protected function createResourceOwner(array $response, AccessToken $token) {}
}
