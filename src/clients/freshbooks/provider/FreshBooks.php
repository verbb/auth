<?php
/**
 * Created by PhpStorm.
 * User: aaflalo
 * Date: 18-05-29
 * Time: 14:04
 */

namespace verbb\auth\clients\freshbooks\provider;

use GuzzleHttp\RequestOptions;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class FreshBooks extends AbstractProvider
{
    use ArrayAccessorTrait,
        BearerAuthorizationTrait;

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://my.freshbooks.com/service/auth/oauth/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://api.freshbooks.com/auth/oauth/token';
    }

    public function getAuthorizationHeaders($token = null): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    protected function getDefaultHeaders(): array
    {
        return ['Api-Version' => 'alpha'];
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.freshbooks.com/auth/api/v1/users/me';
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return ['profile:write'];
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     *
     * @param  ResponseInterface $response
     * @param  array|string      $data Parsed response data
     *
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        static $errors = [
            'error'      => 'error_description',
            'error_type' => 'message',
        ];

        foreach ($errors as $errorKey => $errorMessage) {
            if ($this->getValueByKey($data, $errorKey) && ($message = $this->getValueByKey($data, $errorMessage))) {
                throw new IdentityProviderException($message, $response->getStatusCode(), $response);
            }
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return FreshBooksOwner|ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token): FreshBooksOwner|ResourceOwnerInterface
    {
        return new FreshBooksOwner($response, $token);
    }
}
