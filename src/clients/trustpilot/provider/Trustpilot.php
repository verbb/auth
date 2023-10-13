<?php

namespace verbb\auth\clients\trustpilot\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Trustpilot extends AbstractProvider
{
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'app_enduser';

    use BearerAuthorizationTrait;

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://authenticate.trustpilot.com';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        if (!empty($params['grant_type']) && $params['grant_type'] === 'refresh_token') {
            return 'https://api.trustpilot.com/v1/oauth/oauth-business-users-for-applications/refresh';
        }

        return 'https://api.trustpilot.com/v1/oauth/oauth-business-users-for-applications/accesstoken';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return sprintf('https://api.trustpilot.com/v1/business-units/%s/profileinfo', $token->getResourceOwnerId());
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException($response->getReasonPhrase(), $response->getStatusCode(), $data);
        }
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param array $response
     * @param AccessToken $token
     * @return GenericResourceOwner|ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token): GenericResourceOwner|ResourceOwnerInterface
    {
        return new GenericResourceOwner($response, self::ACCESS_TOKEN_RESOURCE_OWNER_ID);
    }
}
