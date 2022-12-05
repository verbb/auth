<?php

namespace verbb\auth\clients\yelp\tool;

use League\OAuth2\Client\Token\AccessToken;
use verbb\auth\clients\yelp\provider\exception\ProviderConfigurationException;

trait ClientCredentialsOnlyTrait
{
    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        throw ProviderConfigurationException::clientCredentialsOnly();
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     * @codeCoverageIgnore
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        throw ProviderConfigurationException::clientCredentialsOnly();
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param object $response
     * @param AccessToken $token
     * @return YelpResourceOwner
     * @codeCoverageIgnore
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        throw ProviderConfigurationException::clientCredentialsOnly();
    }
}
