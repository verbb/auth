<?php
namespace verbb\auth\clients\freeagent\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class FreeAgent extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected bool $isSandbox = false;

    protected function baseUrl(): string
    {
        return $this->isSandbox ? 'https://api.sandbox.freeagent.com' : 'https://api.freeagent.com';
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->baseUrl() . '/v2/approve_app';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->baseUrl() . '/v2/token_endpoint';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->baseUrl() . '/v2/users/me';
    }

    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function createResourceOwner(array $response, AccessToken $token): FreeAgentResourceOwner
    {
        return new FreeAgentResourceOwner($response);
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

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $request = parent::getAccessTokenRequest($params);
        $uri = $request->getUri()->withUserInfo($this->clientId, $this->clientSecret);

        return $request->withUri($uri);
    }
}
