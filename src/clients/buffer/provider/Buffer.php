<?php

namespace verbb\auth\clients\buffer\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\buffer\provider\exception\BufferProviderException;

class Buffer extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Buffer app base url
     *
     * @const string
     */
    public const BASE_BUFFER_URL = 'https://bufferapp.com';

    /**
     * Buffer API base url
     *
     * @const string
     */
    public const BASE_BUFFER_API_URL = 'https://api.bufferapp.com';

    /**
     * Buffer API version
     *
     * @const string
     */
    public const BUFFER_API_VERSION = 1;

    public function getBaseAuthorizationUrl(): string
    {
        return static::BASE_BUFFER_URL . '/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getApiUrl() . '/oauth2/token.json';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getApiUrl() . '/user.json';
    }

    /**
     * Get the Buffer API URL
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return static::BASE_BUFFER_API_URL . '/' . static::BUFFER_API_VERSION;
    }

    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new BufferProviderException(
                $data['error'],
                isset($data['code']) ? (int) $data['code'] : $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): BufferUser
    {
        return new BufferUser($response);
    }
}
