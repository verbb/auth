<?php 

namespace verbb\auth\clients\aweber\provider;

use GuzzleHttp\Psr7\Uri;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Aweber extends AbstractProvider
{
    /**
     * Default scopes
     *
     * @var array
     */
    public array $defaultScopes = ['account.read list.read'];

    /**
     * Base url for authorization.
     *
     * @var string
     */
    protected string $urlAuthorize = 'https://auth.aweber.com/oauth2/authorize';

    /**
     * Base url for access token.
     *
     * @var string
     */
    protected string $urlAccessToken = 'https://auth.aweber.com/oauth2/token';

    /**
     * Base url for resource owner.
     *
     * @var string
     */
    protected string $urlResourceOwnerDetails = 'https://api.aweber.com/1.0/accounts';

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->urlAuthorize;
    }

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->urlAccessToken;
    }

    /**
     * Get default scopes
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return $this->defaultScopes;
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                ($data['error']['message'] ?? $response->getReasonPhrase()),
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
     * @return AweberResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): AweberResourceOwner
    {
        return new AweberResourceOwner($response);
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
        $uri = new Uri($this->urlResourceOwnerDetails);

        return (string) Uri::withQueryValue($uri, 'access_token', (string) $token);
    }
}