<?php
namespace verbb\auth\clients\front\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Front extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://app.frontapp.com/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://app.frontapp.com/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api2.frontapp.com/me';
    }

    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error_description'] ?? $data['error'],
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): FrontResourceOwner
    {
        return new FrontResourceOwner($response);
    }

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $request = parent::getAccessTokenRequest($params);
        $uri = $request->getUri()->withUserInfo($this->clientId, $this->clientSecret);

        return $request->withUri($uri);
    }
}
