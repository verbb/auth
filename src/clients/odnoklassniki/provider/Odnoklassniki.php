<?php

namespace verbb\auth\clients\odnoklassniki\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Odnoklassniki extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    public string $clientPublic = '';

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://www.odnoklassniki.ru/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://api.odnoklassniki.ru/oauth/token.do';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $param = 'application_key='.$this->clientPublic
            .'&fields=uid,name,first_name,last_name,location,pic_3,gender,locale,photo_id'
            .'&method=users.getCurrentUser';
        $sign = md5(str_replace('&', '', $param).md5($token.$this->clientSecret));
        return 'http://api.odnoklassniki.ru/fb.do?'.$param.'&access_token='.$token.'&sig='.$sign;
    }

    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error_code'])) {
            throw new IdentityProviderException($data['error_msg'], $data['error_code'], $response);
        }

        if (isset($data['error'])) {
            throw new IdentityProviderException($data['error'].': '.$data['error_description'],
                $response->getStatusCode(), $response);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): OdnoklassnikiResourceOwner|ResourceOwnerInterface
    {
        return new OdnoklassnikiResourceOwner($response);
    }
}
