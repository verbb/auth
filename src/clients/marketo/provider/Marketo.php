<?php
namespace verbb\auth\clients\marketo\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Marketo extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected string $apiDomain = '';

    public function getBaseAuthorizationUrl(): string
    {
        return $this->getApiUrl() . 'identity/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getApiUrl() . 'identity/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return ''; // Marketo does not provide user details via token
    }

    public function getApiUrl(): string
    {
        return rtrim($this->apiDomain, '/') . '/';
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

    protected function createResourceOwner(array $response, AccessToken $token): MarketoResourceOwner
    {
        return new MarketoResourceOwner($response);
    }

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $request = parent::getAccessTokenRequest($params);
        $uri = $request->getUri()->withUserInfo($this->clientId, $this->clientSecret);

        return $request->withUri($uri);
    }
}
