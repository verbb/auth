<?php
namespace verbb\auth\clients\suitecrm\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SuiteCrm extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected string $apiDomain = '';

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getApiUrl() . 'Api/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getApiUrl() . 'Api/V8/module/User/me';
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->getApiUrl() . 'Api/authorize';
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

    protected function createResourceOwner(array $response, AccessToken $token): SuiteCrmResourceOwner
    {
        return new SuiteCrmResourceOwner($response);
    }

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $request = parent::getAccessTokenRequest($params);

        // Keep credentials out of the URI so guarded transports can validate the token endpoint.
        return $request->withHeader('Authorization', 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret));
    }
}
