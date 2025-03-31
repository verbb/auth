<?php
namespace verbb\auth\clients\onecrm\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OneCrm extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected string $apiDomain = '';

    public function getBaseAuthorizationUrl(): string
    {
        return $this->getApiUrl() . 'auth/user/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getApiUrl() . 'auth/user/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getApiUrl() . 'accounts';
    }

    public function getApiUrl(): string
    {
        return rtrim($this->apiDomain, '/') . '/api.php/';
    }

    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                ($data['error']['message'] ?? $response->getReasonPhrase()),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): OneCrmResourceOwner
    {
        return new OneCrmResourceOwner($response);
    }

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $method  = $this->getAccessTokenMethod();
        $url = $this->getAccessTokenUrl($params);

        // 1CRM required the access token request to use JSON, not the traditional `application/x-www-form-urlencoded`
        $options = [
            'headers' => [
                'content-type' => 'application/json',
            ],
            'body' => json_encode($params),
        ];

        return $this->getRequest($method, $url, $options);
    }
}
