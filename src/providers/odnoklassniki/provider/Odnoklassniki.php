<?php

namespace verbb\auth\providers\odnoklassniki\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Odnoklassniki extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    public $clientPublic = '';

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://www.odnoklassniki.ru/oauth/authorize';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://api.odnoklassniki.ru/oauth/token.do';
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $param = 'application_key='.$this->clientPublic
            .'&fields=uid,name,first_name,last_name,location,pic_3,gender,locale,photo_id'
            .'&method=users.getCurrentUser';
        $sign = md5(str_replace('&', '', $param).md5($token.$this->clientSecret));
        return 'http://api.odnoklassniki.ru/fb.do?'.$param.'&access_token='.$token.'&sig='.$sign;
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
            throw new IdentityProviderException($data['error'].': '.$data['error_description'],
                $response->getStatusCode(), $response);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new OdnoklassnikiResourceOwner($response);
    }
}
