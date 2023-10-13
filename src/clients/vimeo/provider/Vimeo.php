<?php

namespace verbb\auth\clients\vimeo\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Vimeo extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://api.vimeo.com/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://api.vimeo.com/oauth/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.vimeo.com/me?access_token=' . $token;
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function getDefaultScopes(): array
    {
        return ['public'];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (!empty($data['error'])) {
            $error = $data['error']['message'] ?? '';
            $code = isset($data['error']['code']) ? (int)$data['error']['code'] : 0;
            throw new IdentityProviderException($error, $code, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): VimeoResourceOwner|ResourceOwnerInterface
    {
        return new VimeoResourceOwner($response, $token);
    }
}
