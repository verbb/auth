<?php

namespace verbb\auth\clients\yahoo\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Yahoo extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'xoauth_yahoo_guid';

    /*
    https://developer.yahoo.com/oauth2/guide/flows_authcode/#step-2-get-an-authorization-url-and-authorize-access
    */
    protected string $language = "en-us";

    private string $imageSize = '192x192';

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://api.login.yahoo.com/oauth2/request_auth';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://api.login.yahoo.com/oauth2/get_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $guid = $token->getResourceOwnerId();

        return 'https://social.yahooapis.com/v1/user/' . $guid . '/profile?format=json';
    }

    /**
     * Get user image from provider
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return array
     */
    protected function getUserImage(array $response, AccessToken $token): array
    {
        $guid = $token->getResourceOwnerId();

        $url = 'https://social.yahooapis.com/v1/user/' . $guid . '/profile/image/' . $this->imageSize . '?format=json';

        $request = $this->getAuthenticatedRequest('get', $url, $token);

        return $this->getResponse($request);
    }

    protected function getAuthorizationParameters(array $options): array
    {
        $params = parent::getAuthorizationParameters($options);

        $params['language'] = $options['language'] ?? $this->language;

        return $params;
    }

    protected function getDefaultScopes(): array
    {
        /*
           No scope is required. scopes are part of APP Settings.
        */
        return [];
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (!empty($data['error'])) {
            $code = 0;
            $error = $data['error'];

            if (is_array($error)) {
                /*
                   No code is returned in the error
                */
                $code = -1;
                $error = $error['description'];
            }
            throw new IdentityProviderException($error, $code, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): YahooUser|ResourceOwnerInterface
    {
        $user = new YahooUser($response);

        $imageUrl = $this->getUserImageUrl($response, $token);

        return $user->setImageURL($imageUrl);
    }

    /**
     * Get user image url from provider, if available
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return string|null
     */
    protected function getUserImageUrl(array $response, AccessToken $token): ?string
    {
        $image = $this->getUserImage($response, $token);

        return $image['image']['imageUrl'] ?? null;
    }
}
