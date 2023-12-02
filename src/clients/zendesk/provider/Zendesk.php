<?php namespace verbb\auth\clients\zendesk\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Zendesk extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected string $subdomain = '';

    /**
     * @var string Key used in a token response to identify the resource owner.
     */
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *     Individual providers may introduce more options, as needed.
     * @param array $collaborators An array of collaborators that may be used to
     *     override this provider's default behavior. Collaborators include
     *     `grantFactory`, `requestFactory`, `httpClient`, and `randomFactory`.
     *     Individual providers may introduce more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (empty($this->subdomain)) {
            throw new Exception\ProviderConfigurationException(
                'No subdomain has been configured for this Zendesk provider; it has to have a subdomain.'
            );
        }
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://'.$this->subdomain.'.zendesk.com/oauth/authorizations/new';
    }

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://'.$this->subdomain.'.zendesk.com/oauth/tokens';
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
        return 'https://'.$this->subdomain.'.zendesk.com/api/v2/users/me.json';
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
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            throw new IdentityProviderException(
                $data['description'] ?? $response->getReasonPhrase(),
                $statusCode,
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return ZendeskResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): ZendeskResourceOwner
    {
        return new ZendeskResourceOwner($response);
    }

    /**
     * Retrieves currently configured subdomain.
     *
     * @return string
     */
    public function getSubdomain(): string
    {
        return $this->subdomain;
    }

    /**
     * Updates currently configured subdomain.
     *
     * @param string $subdomain
     *
     * @return Zendesk
     */
    public function setSubdomain(string $subdomain): Zendesk
    {
        if (!empty($subdomain)) {
            $this->subdomain = $subdomain;
        }

        return $this;
    }
}
