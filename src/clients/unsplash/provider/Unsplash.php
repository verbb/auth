<?php

namespace verbb\auth\clients\unsplash\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Exception;

/**
 * Class Unsplash
 * @package Unsplash\OAuth2\Client\Provider
 * @see https://unsplash.com/documentation#user-authentication
 * @see http://oauth2-client.thephpleague.com/providers/implementing
 */
class Unsplash extends AbstractProvider
{
    /**
     * Used for public scoped requests
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://unsplash.com/oauth/authorize';
    }

    /**
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://unsplash.com/oauth/token';
    }

    /**
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.unsplash.com/me?access_token=' . $token;
    }

    /**
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return ['public'];
    }

    /**
     * @param ResponseInterface $response
     * @param array|string $data
     * @throws Exception
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (! empty($data['error'])) {
            $message = $data['error'].": ".$data['error_description'];
            throw new Exception($message);
        }
    }

    /**
     * @param array $response
     * @param AccessToken $token
     * @return UnsplashResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): UnsplashResourceOwner
    {
        return new UnsplashResourceOwner($response);
    }

    /**
     * @return string
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }
}