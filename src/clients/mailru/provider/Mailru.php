<?php

namespace verbb\auth\clients\mailru\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Mailru extends AbstractProvider
{
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://connect.mail.ru/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://connect.mail.ru/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $param = 'app_id=' . $this->clientId . '&method=users.getInfo&secure=1&session_key=' . $token->getToken();
        $sign = md5(str_replace('&', '', $param) . $this->clientSecret);
        return 'http://www.appsmail.ru/platform/api?' . $param . '&sig=' . $sign;
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
            throw new IdentityProviderException($data['error'],
                $response->getStatusCode(), $response);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): MailruResourceOwner|ResourceOwnerInterface
    {
        return new MailruResourceOwner($response);
    }
}
