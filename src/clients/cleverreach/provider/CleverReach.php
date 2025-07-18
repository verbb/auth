<?php
namespace verbb\auth\clients\cleverreach\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CleverReach extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://rest.cleverreach.com/oauth/authorize.php';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://rest.cleverreach.com/oauth/token.php';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://rest.cleverreach.com/v3/me.json';
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

    protected function createResourceOwner(array $response, AccessToken $token): CleverReachResourceOwner
    {
        return new CleverReachResourceOwner($response);
    }

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $request = parent::getAccessTokenRequest($params);
        $uri = $request->getUri()->withUserInfo($this->clientId, $this->clientSecret);

        return $request->withUri($uri);
    }
}
