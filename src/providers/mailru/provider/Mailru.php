<?php

namespace verbb\auth\providers\mailru\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Mailru extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://connect.mail.ru/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://connect.mail.ru/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $param = 'app_id=' . $this->clientId . '&method=users.getInfo&secure=1&session_key=' . $token->getToken();
        $sign = md5(str_replace('&', '', $param) . $this->clientSecret);
        return 'http://www.appsmail.ru/platform/api?' . $param . '&sig=' . $sign;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error_code'])) {
            throw new IdentityProviderException($data['error_msg'], $data['error_code'], $response);
        } elseif (isset($data['error'])) {
            throw new IdentityProviderException($data['error'],
                $response->getStatusCode(), $response);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MailruResourceOwner($response);
    }
}
