<?php

namespace verbb\auth\clients\mastodon\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Mastodon extends AbstractProvider
{

    use BearerAuthorizationTrait;

    /**
     * Mastodon Instance URL
     * ex) https://mstdn.jp
     * @var string
     */
    protected mixed $instance;


    /**
     * @var array
     */
    protected mixed $scope;


    /**
     * Mastodon constructor.
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (isset($options['instance'])) {
            $this->instance = $options['instance'];
        }

        if (isset($options['scope'])) {
            $this->scope = $options['scope'];
        }
    }


    /**
     * Get authorization url to begin OAuth flow
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->instance.'/oauth/authorize';
    }

    /**
     * Get access token url to retrieve token
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->instance.'/oauth/token';
    }


    /**
     * Get provider url to fetch user details
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->instance . '/api/v1/accounts/verify_credentials';
    }

    /**
     * Get the default scopes used by this provider
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return $this->scope ?? ['scope' => 'read'];
    }

    /**
     * Check a provider response for errors
     * @throws IdentityProviderException
     * @param ResponseInterface $response
     * @param array|string $data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw  new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
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
     * @return ResourceOwnerInterface|MastodonResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): MastodonResourceOwner|ResourceOwnerInterface
    {
        return new MastodonResourceOwner($response);
    }


    /**
     * Requests resource owner details.
     *
     * @param AccessToken $token
     * @return mixed
     */
    protected function fetchResourceOwnerDetails(AccessToken $token): mixed
    {
        return parent::fetchResourceOwnerDetails($token);
    }


    /**
     * Builds the authorization URL.
     *
     * @param array $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        return parent::getAuthorizationUrl($options);
    }


    /**
     * get mastodon instance url
     * @return string
     */
    public function getInstanceUrl() : string
    {
        return $this->instance;
    }
}
