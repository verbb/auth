<?php

namespace verbb\auth\clients\disqus\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Disqus extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://disqus.com/api/oauth/2.0/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://disqus.com/api/oauth/2.0/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://disqus.com/api/3.0/users/details.json?access_token' . $token;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultScopes()
    {
        return ['read'];
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $error = isset($data['error']['message']) ? $data['error']['message'] : '';
            $code = isset($data['error']['code']) ? intval($data['error']['code']) : 0;
            throw new IdentityProviderException($error, $code, $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new DisqusResourceOwner($response, $token);
    }
}
