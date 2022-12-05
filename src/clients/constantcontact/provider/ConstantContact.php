<?php

namespace verbb\auth\clients\constantcontact\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class ConstantContact extends AbstractProvider
{

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

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
    }

    public function getBaseAuthorizationUrl()
    {
        return 'https://authz.constantcontact.com/oauth2/default/v1/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://authz.constantcontact.com/oauth2/default/v1/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.cc.email/v3/account/summary';
    }

    public function getDefaultScopes()
    {
        return [];
    }

    public function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['errors'])) {
            throw new IdentityProviderException($data['errors'], 0, $data);
        }

        return $data;
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ConstantContactAccount($response);
    }
}
