<?php namespace verbb\auth\clients\uber\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\uber\provider\League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Uber extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Default scopes
     *
     * @var array
     */
    public array $defaultScopes = [];

    /**
     * Uber Api version
     *
     * @var string
     */
    public string $version = 'v1';

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://login.uber.com/oauth/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://login.uber.com/oauth/token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.uber.com/'.$this->version.'/me';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return $this->defaultScopes;
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ' '
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Check a provider response for errors.
     *
     * @link https://developer.uber.com/v1/api-reference/
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $acceptableStatuses = [200, 201];

        if (!in_array($response->getStatusCode(), $acceptableStatuses)) {
            throw new IdentityProviderException(
                $data['message'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return UberResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): UberResourceOwner
    {
        return new UberResourceOwner($response);
    }
}
