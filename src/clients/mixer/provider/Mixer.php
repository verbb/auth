<?php

namespace verbb\auth\clients\mixer\provider;

use verbb\auth\clients\mixer\entity\MixerUser;
use League\OAuth2\Client\Provider\AbstractProvider;
use verbb\auth\clients\mixer\provider\exception\MixerIdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Mixer
 * @package Morgann\OAuth2\Client\Mixer\Provider
 */
class Mixer extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * OAuth domain
     * @var string
     */
    public $oauthDomain = 'https://mixer.com/oauth';

    /**
     * Api domain
     * @var string
     */
    public $apiDomain = 'https://mixer.com/api/v1';

    /**
     *  Scopes
     * @var array
     */
    public $scopes = [
        'user:details:self'
    ];

    /**
     * Get authorization url to begin OAuth flow
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->oauthDomain.'/authorize';
    }

    /**
     * Get access token url to retrieve token
     * @param  array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->apiDomain.'/oauth/token';
    }

    /**
     * Get provider url to fetch user details
     * @param  AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getAuthenticatedUrlForEndpoint('/users/current', $token);
    }

    /**
     * Get the full uri with appended oauth_token query string
     * @param string $endpoint | with leading slash
     * @param AccessToken $token
     * @return string
     */
    public function getAuthenticatedUrlForEndpoint($endpoint, AccessToken $token)
    {
        return $this->apiDomain.$endpoint.'?oauth_token='.$token->getToken();
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     * @return string Scope separator
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Get the default scopes used by this provider.
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     * @return array
     */
    protected function getDefaultScopes()
    {
        return $this->scopes;
    }

    /**
     * Checks response
     * @param ResponseInterface $response
     * @param array|string $data
     * @return \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Morgann\OAuth2\Client\Mixer\Provider\Exception\MixerIdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            return MixerIdentityProviderException::fromResponse($response, $data['error']);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     * @param array $response
     * @param AccessToken $token
     * @return MixerUser
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MixerUser($response);
    }
}
