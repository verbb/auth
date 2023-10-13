<?php
namespace verbb\auth\clients\thirtysevensignals\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class Thirtysevensignals extends AbstractProvider
{
    use ArrayAccessorTrait,
        BearerAuthorizationTrait;

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://launchpad.37signals.com/authorization/new';
    }

    /**
     * Builds the authorization URL's query string.
     *
     * @param  array $params Query parameters
     * @return string Query string
     */
    protected function getAuthorizationQuery(array $params): string
    {
        $params['type'] = 'web_server';
        return $this->buildQueryString($params);
    }

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://launchpad.37signals.com/authorization/token';
    }

    /**
     * Returns the request body for requesting an access token.
     *
     * @param  array $params
     * @return string
     */
    protected function getAccessTokenBody(array $params): string
    {
        $params['type'] = ($params['grant_type'] === 'refresh_token') ? 'refresh' : 'web_server';
        return $this->buildQueryString($params);
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
        return 'https://launchpad.37signals.com/authorization.json';
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
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $errors = [
            'error_description',
            'error.message',
            'error',
        ];

        array_map(function ($error) use ($response, $data) {
            if ($message = $this->getValueByKey($data, $error)) {
                throw new IdentityProviderException($message, $response->getStatusCode(), $response);
            }
        }, $errors);
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return ThirtysevensignalsResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): ThirtysevensignalsResourceOwner
    {
        return new ThirtysevensignalsResourceOwner($response);
    }

    /**
     * Returns a prepared request for requesting an access token.
     *
     * @param array $params Query string parameters
     * @return RequestInterface
     */
    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $request = parent::getAccessTokenRequest($params);
        $uri = $request->getUri()
            ->withUserInfo($this->clientId, $this->clientSecret);

        return $request->withUri($uri);
    }
}
