<?php

namespace verbb\auth\clients\github\provider;

use verbb\auth\clients\github\provider\exception\GitHubIdentityProviderException;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class GitHub extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Domain
     *
     * @var string
     */
    public string $domain = 'https://github.com';

    /**
     * Api domain
     *
     * @var string
     */
    public string $apiDomain = 'https://api.github.com';

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->domain . '/login/oauth/authorize';
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
        return $this->domain . '/login/oauth/access_token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        if ($this->domain === 'https://github.com') {
            return $this->apiDomain . '/user';
        }
        return $this->domain . '/api/v3/user';
    }

    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $response = parent::fetchResourceOwnerDetails($token);

        if (empty($response['email'])) {
            $url = $this->getResourceOwnerDetailsUrl($token) . '/emails';

            $request = $this->getAuthenticatedRequest(self::METHOD_GET, $url, $token);

            $responseEmail = $this->getParsedResponse($request);

            $response['email'] = $responseEmail[0]['email'] ?? null;
        }

        return $response;
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
        return [
            'user.email',
        ];
    }

    /**
     * Check a provider response for errors.
     *
     * @link   https://developer.github.com/v3/#client-errors
     * @link   https://developer.github.com/v3/oauth/#common-errors-for-the-access-token-request
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array             $data     Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw GitHubIdentityProviderException::clientException($response, $data);
        }

        if (isset($data['error'])) {
            throw GitHubIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param  array       $response
     * @param  AccessToken $token
     * @return ResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): GithubResourceOwner
    {
        $user = new GitHubResourceOwner($response);

        return $user->setDomain($this->domain);
    }
}
