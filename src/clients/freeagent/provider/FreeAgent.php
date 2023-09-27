<?php
namespace verbb\auth\clients\freeagent\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class FreeAgent extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected $isSandbox = false;

    protected function baseUrl()
    {
        return $this->isSandbox ? 'https://api.sandbox.freeagent.com' : 'https://api.freeagent.com';
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->baseUrl() . '/v2/approve_app';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->baseUrl() . '/v2/token_endpoint';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->baseUrl() . '/v2/users/me';
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new FreeAgentResourceOwner($response);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                (isset($data['error']['message']) ? $data['error']['message'] : $response->getReasonPhrase()),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function getAccessTokenRequest(array $params)
    {
        $request = parent::getAccessTokenRequest($params);
        $uri = $request->getUri()->withUserInfo($this->clientId, $this->clientSecret);

        return $request->withUri($uri);
    }
}
