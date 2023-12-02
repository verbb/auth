<?php 
namespace verbb\auth\clients\aweber\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Aweber extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://auth.aweber.com/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://auth.aweber.com/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.aweber.com/1.0/accounts';
    }

    protected function getDefaultScopes(): array
    {
        return ['account.read', 'list.read'];
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

    protected function createResourceOwner(array $response, AccessToken $token): AweberResourceOwner
    {
        return new AweberResourceOwner($response);
    }

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $request = parent::getAccessTokenRequest($params);
        $uri = $request->getUri()->withUserInfo($this->clientId, $this->clientSecret);

        return $request->withUri($uri);
    }
}