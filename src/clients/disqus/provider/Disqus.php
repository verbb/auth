<?php

namespace verbb\auth\clients\disqus\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Disqus extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://disqus.com/api/oauth/2.0/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://disqus.com/api/oauth/2.0/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://disqus.com/api/3.0/users/details.json?access_token' . $token;
    }

    protected function getDefaultScopes(): array
    {
        return ['read'];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (!empty($data['error'])) {
            $error = $data['error']['message'] ?? '';
            $code = isset($data['error']['code']) ? (int)$data['error']['code'] : 0;
            throw new IdentityProviderException($error, $code, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): DisqusResourceOwner|ResourceOwnerInterface
    {
        return new DisqusResourceOwner($response, $token);
    }
}
