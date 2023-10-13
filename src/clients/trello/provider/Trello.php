<?php
namespace verbb\auth\clients\trello\provider;

use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Token\AccessToken;

class Trello extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://trello.com/1/OAuthAuthorizeToken';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://trello.com/1/OAuthGetAccessToken';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.trello.com/1/members/me';
    }

    public function getDefaultScopes(): array
    {
        return [];
    }

    public function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['message'] ?? $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    public function createResourceOwner(array $response, AccessToken $token): TrelloUser
    {
        return new TrelloUser($response);
    }
}