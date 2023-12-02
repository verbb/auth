<?php
namespace verbb\auth\clients\onecrm\provider;

use GuzzleHttp\Client as HttpClient;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

class OneCrm extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected string $apiDomain = null;

    public function getBaseAuthorizationUrl(): string
    {
        return $this->getApiUrl() . 'auth/user/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getApiUrl() . 'auth/user/access_token';
    }

    public function getApiUrl(): string
    {
        return rtrim($this->apiDomain, '/') . '/api.php/';
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
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
            'body' => json_decode($params),
        ];

        return $this->getRequest($method, $url, $options);
    }
}
