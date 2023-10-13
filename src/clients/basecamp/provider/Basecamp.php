<?php

namespace verbb\auth\clients\basecamp\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Basecamp extends AbstractProvider
{
    use ArrayAccessorTrait,
        BearerAuthorizationTrait;

    /**
     * Basecamp environment specific host; defaults to api
     *
     * @var string
     */
    protected string $host = 'https://launchpad.37signals.com';

    /**
     * Type of flow
     *
     * Basecamp supports the web_server and user_agent flows, not the
     * client_credentials or device flows.
     *
     * @var string
     * @see https://github.com/basecamp/api/blob/master/sections/authentication.md
     */
    protected string $type = 'web_server';

    /**
     * Returns authorization parameters based on provided options.
     *
     * @param  array $options
     * @return array Authorization parameters
     */
    protected function getAuthorizationParameters(array $options): array
    {
        $options = parent::getAuthorizationParameters($options);

        $options['type'] = $this->type;

        return $options;
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->getHost() . '/authorization/new';
    }

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        $params = array_merge([
            'type' => $this->type,
        ], $params);

        $query = array_filter($params, function ($key) {
            return in_array($key, ['type', 'refresh_token']);
        }, ARRAY_FILTER_USE_KEY);

        return $this->getHost() . '/authorization/token?'.http_build_query($query);
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
        return $this->getHost() . '/authorization.json';
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
        return [];
    }

    /**
     * Returns a cleaned host.
     *
     * @return string
     */
    public function getHost(): string
    {
        return rtrim($this->host, '/');
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
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        // At the time of initial implementation the possible error payloads returned
        // by Basecamp were not very well documented. This method will need some
        // improvement as the API continues to mature.
        if ($response->getStatusCode() != 200) {
            throw new IdentityProviderException('Unexpected response code', $response->getStatusCode(), $response);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     */
    protected function createResourceOwner(array $response, AccessToken $token): BasecampResourceOwner
    {
        return new BasecampResourceOwner($response);
    }
}
