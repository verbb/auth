<?php

namespace verbb\auth\clients\jira\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use verbb\auth\clients\jira\provider\exception\JiraIdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Jira extends AbstractProvider
{
    use ArrayAccessorTrait;
    use BearerAuthorizationTrait;
    
    /**
     *
     * @var string URL used for non-OAuth API calls
     */
    protected string $apiUrl = '';

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array $data Parsed response data
     *
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw JiraIdentityProviderException::clientException($response, $data);
        }

        if (isset($data['error'])) {
            throw JiraIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return JiraResourceOwner|ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token): JiraResourceOwner|ResourceOwnerInterface
    {
        return new JiraResourceOwner($response);
    }
    
    /**
     *
     * @return string URL used for non-OAuth API calls
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Get access token url to retrieve token
     *
     * @param  array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://auth.atlassian.com/oauth/token';
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://auth.atlassian.com/authorize?audience=api.atlassian.com&prompt=consent';
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
        return ['read:jira-user'];
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
        $request = $this->getAuthenticatedRequest(
            self::METHOD_GET,
            'https://api.atlassian.com/oauth/token/accessible-resources',
            $token
        );

        $response = $this->getParsedResponse($request);

        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }
        
        $cloudId = $this->getValueByKey($response, '0.id');
        
        $this->setApiUrl('https://api.atlassian.com/ex/jira/'.$cloudId);

        return $this->getApiUrl().'/rest/api/3/myself';
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
     *
     * @param string $url URL used for non-OAuth API calls
     */
    protected function setApiUrl(string $url): void
    {
        $this->apiUrl = $url;
    }
}
