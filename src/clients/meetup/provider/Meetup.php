<?php

declare(strict_types=1);

namespace verbb\auth\clients\meetup\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Meetup extends AbstractProvider
{
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://secure.meetup.com/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://secure.meetup.com/oauth2/access';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return sprintf('https://api.meetup.com/2/member/self?access_token=%s', $token->getToken());
    }

    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (is_array($data) === false) {
            throw new IdentityProviderException(
                'Invalid response body',
                0,
                (string)$data
            );
        }

        if ($response->getStatusCode() !== 200) {
            throw new IdentityProviderException(
                'Invalid response status',
                0,
                $data
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return ResourceOwner::fromArray($response);
    }
}
