<?php
namespace verbb\auth\clients\hubspot\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class HubSpot extends AbstractProvider
{
    protected string $baseApiUrl = 'https://api.hubapi.com/oauth/v1';

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://app.hubspot.com/oauth/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->baseApiUrl . '/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->baseApiUrl . '/access-tokens/' . $token->getToken();
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This provider doesn't specify default scopes. This is here to satisfy
     * the provider interface.
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * @param ResponseInterface $response
     * @param array|string      $data
     *
     * @throws IdentityProviderException
     */
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

    /**
     * Generate a minimal user object from a successful user details request.
     *
     * @param array       $response
     * @param AccessToken $token
     *
     * @return HubSpotResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): HubSpotResourceOwner
    {
        return new HubSpotResourceOwner($response);
    }
}
