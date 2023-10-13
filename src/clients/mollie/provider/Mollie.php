<?php namespace verbb\auth\clients\mollie\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use DomainException;

class Mollie extends AbstractProvider
{
    /**
     * Version of this client.
     */
    public const CLIENT_VERSION = "2.6.0";

    /**
     * The base url to the Mollie API.
     *
     * @const string
     */
    public const MOLLIE_API_URL = 'https://api.mollie.com';

    /**
     * The base url to the Mollie web application.
     *
     * @const string
     */
    public const MOLLIE_WEB_URL = 'https://www.mollie.com';

    /**
     * The prefix for the Client ID
     *
     * @const string
     */
    public const CLIENT_ID_PREFIX = 'app_';

    /**
     * @var string HTTP method used to revoke tokens.
     */
    public const METHOD_DELETE = 'DELETE';

    /**
     * @var string Token type hint for Mollie access tokens.
     */
    public const TOKEN_TYPE_ACCESS = 'access_token';

    /**
     * @var string Token type hint for Mollie refresh tokens.
     */
    public const TOKEN_TYPE_REFRESH = 'refresh_token';

    /**
     * Shortcuts to the available Mollie scopes.
     *
     * In order to access the Mollie API endpoints on behalf of your app user, your
     * app should request the appropriate scope permissions.
     *
     * @see https://docs.mollie.com/oauth/permissions
     */
    public const SCOPE_PAYMENTS_READ = 'payments.read';
    public const SCOPE_PAYMENTS_WRITE = 'payments.write';
    public const SCOPE_REFUNDS_READ = 'refunds.read';
    public const SCOPE_REFUNDS_WRITE = 'refunds.write';
    public const SCOPE_CUSTOMERS_READ = 'customers.read';
    public const SCOPE_CUSTOMERS_WRITE = 'customers.write';
    public const SCOPE_MANDATES_READ = 'mandates.read';
    public const SCOPE_MANDATES_WRITE = 'mandates.write';
    public const SCOPE_SUBSCRIPTIONS_READ = 'subscriptions.read';
    public const SCOPE_SUBSCRIPTIONS_WRITE = 'subscriptions.write';
    public const SCOPE_PROFILES_READ = 'profiles.read';
    public const SCOPE_PROFILES_WRITE = 'profiles.write';
    public const SCOPE_INVOICES_READ = 'invoices.read';
    public const SCOPE_SETTLEMENTS_READ = 'settlements.read';
    public const SCOPE_ORDERS_READ = 'orders.read';
    public const SCOPE_ORDERS_WRITE = 'orders.write';
    public const SCOPE_SHIPMENTS_READ = 'shipments.read';
    public const SCOPE_SHIPMENTS_WRITE = 'shipments.write';
    public const SCOPE_ORGANIZATIONS_READ = 'organizations.read';
    public const SCOPE_ORGANIZATIONS_WRITE = 'organizations.write';
    public const SCOPE_ONBOARDING_READ = 'onboarding.read';
    public const SCOPE_ONBOARDING_WRITE = 'onboarding.write';
    public const SCOPE_PAYMENT_LINKS_READ = 'payment-links.read';
    public const SCOPE_PAYMENT_LINKS_WRITE = 'payment-links.write';

    /**
     * @var string
     */
    private string $mollieApiUrl = self::MOLLIE_API_URL;

    /**
     * @var string
     */
    private string $mollieWebUrl = self::MOLLIE_WEB_URL;

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (isset($options["clientId"]) && !str_starts_with($options["clientId"], self::CLIENT_ID_PREFIX)) {
            throw new DomainException("Mollie needs the client ID to be prefixed with " . self::CLIENT_ID_PREFIX . ".");
        }
    }

    /**
     * Define Mollie api URL
     *
     * @param string $url
     * @return Mollie
     */
    public function setMollieApiUrl(string $url): Mollie
    {
        $this->mollieApiUrl = $url;

        return $this;
    }

    /**
     * Define Mollie web URL
     *
     * @param string $url
     * @return Mollie
     */
    public function setMollieWebUrl(string $url): Mollie
    {
        $this->mollieWebUrl = $url;

        return $this;
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->mollieWebUrl . '/oauth2/authorize';
    }

    /**
     * Returns the base URL for requesting or revoking an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->mollieApiUrl . '/oauth2/tokens';
    }

    /**
     * Returns the URL for requesting the app user's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return static::MOLLIE_API_URL . '/v2/organizations/me';
    }

    /**
     * Revoke a Mollie access token.
     *
     * @param string $accessToken
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function revokeAccessToken(string $accessToken): ResponseInterface
    {
        return $this->revokeToken(self::TOKEN_TYPE_ACCESS, $accessToken);
    }

    /**
     * Revoke a Mollie refresh token.
     *
     * @param string $refreshToken
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function revokeRefreshToken(string $refreshToken): ResponseInterface
    {
        return $this->revokeToken(self::TOKEN_TYPE_REFRESH, $refreshToken);
    }

    /**
     * Revoke a Mollie access token or refresh token.
     *
     * @param string $type
     * @param string $token
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function revokeToken(string $type, string $token): ResponseInterface
    {
        return $this->getRevokeTokenResponse([
            'token_type_hint' => $type,
            'token' => $token,
        ]);
    }

    /**
     * Sends a token revocation request and returns an response instance.
     *
     * @param array $params
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function getRevokeTokenResponse(array $params): ResponseInterface
    {
        $params['client_id'] = $this->clientId;
        $params['client_secret'] = $this->clientSecret;
        $params['redirect_uri'] = $this->redirectUri;

        $options = ['headers' => ['content-type' => 'application/x-www-form-urlencoded']];
        $options['body'] = $this->buildQueryString($params);

        $request = $this->getRequest(
            self::METHOD_DELETE,
            $this->getBaseAccessTokenUrl([]),
            $options
        );

        return $this->getHttpClient()->send($request);
    }

    /**
     * The Mollie OAuth provider requests access to the organizations.read scope
     * by default to enable retrieving the app user's details.
     *
     * @return string[]
     */
    protected function getDefaultScopes(): array
    {
        return [
            self::SCOPE_ORGANIZATIONS_READ,
        ];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ','
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param ResponseInterface $response
     * @param array|string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            if (isset($data['error'])) {
                if (isset($data['error']['type']) && isset($data['error']['message'])) {
                    $message = sprintf('[%s] %s', $data['error']['type'], $data['error']['message']);
                } else {
                    $message = $data['error'];
                }

                if (isset($data['error']['field'])) {
                    $message .= sprintf(' (field: %s)', $data['error']['field']);
                }
            } else {
                $message = $response->getReasonPhrase();
            }

            throw new IdentityProviderException($message, $response->getStatusCode(), $response);
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return MollieResourceOwner|ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token): MollieResourceOwner|ResourceOwnerInterface
    {
        return new MollieResourceOwner($response);
    }

    /**
     * Returns required authorization headers plus Mollie user agent strings.
     *
     * @param AccessTokenInterface|string|null $token Either a string or an access token instance
     * @return array
     */
    protected function getAuthorizationHeaders($token = null): array
    {
        $userAgent = implode(' ', [
            "MollieOAuth2PHP/" . self::CLIENT_VERSION,
            "PHP/" . PHP_VERSION,
        ]);

        return [
            'Authorization' => 'Bearer ' . $token,
            'User-Agent' => $userAgent,
        ];
    }
}
