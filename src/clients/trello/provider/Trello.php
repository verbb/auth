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

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    public function getBaseAuthorizationUrl()
    {
        return 'https://trello.com/1/OAuthAuthorizeToken';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://trello.com/1/OAuthGetAccessToken';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.trello.com/1/members/me';
    }

    public function getDefaultScopes()
    {
        return [];
    }

    public function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                isset($data['message']) ? $data['message'] : $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    public function createResourceOwner(array $response, AccessToken $token)
    {
        return new TrelloUser($response);
    }
}