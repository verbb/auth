<?php
namespace verbb\auth\clients\intercom\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Intercom extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://app.intercom.com/oauth';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://api.intercom.io/auth/eagle/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.intercom.io/me';
    }

    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function getDefaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $statusCode = $response->getStatusCode();

        if (empty($data['errors']) && $statusCode == 200) {
            return;
        }

        throw new IdentityProviderException(
            $data['errors'][0]['message'] ?: $response->getReasonPhrase(),
            $statusCode,
            $response
        );
    }

    protected function createResourceOwner(array $response, AccessToken $token): IntercomResourceOwner
    {
        return new IntercomResourceOwner($response);
    }

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        $request = parent::getAccessTokenRequest($params);
        $uri = $request->getUri()->withUserInfo($this->clientId, $this->clientSecret);

        return $request->withUri($uri);
    }
}
