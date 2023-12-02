<?php

namespace verbb\auth\clients\shopify\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Shopify extends AbstractProvider
{

    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * @var string This will be prepended to the base uri.
     * @link https://help.shopify.com/api/guides/authentication/oauth#asking-for-permission
     */
    protected string $shop = '';

    /**
     * @var string If set, this will be sent to shopify as the "per-user" parameter.
     * @link https://help.shopify.com/api/guides/authentication/oauth#asking-for-permission
     */
    protected string $accessType = '';

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://'.$this->shop.'/admin/oauth/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://'.$this->shop.'/admin/oauth/access_token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://'.$this->shop.'/admin/shop.json';
    }

    public function getAuthorizationParameters(array $options): array
    {
        $option = (!empty($this->accessType) && $this->accessType != 'offline') ? 'per-user' : null;

        return array_merge(
            parent::getAuthorizationParameters($options),
            array_filter([
                'option' => $option
            ])
        );
    }

    public function getDefaultScopes(): array
    {
        return [
            'read_content',
            'read_products',
        ];
    }

    public function getScopeSeparator(): string
    {
        return parent::getScopeSeparator();
    }

    public function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['errors'])) {
            throw new IdentityProviderException($data['errors'], 0, $data);
        }

        return $data;
    }

    protected function createResourceOwner(array $response, AccessToken $token): ShopifyStore
    {
        return new ShopifyStore($response);
    }

    /**
     * Returns the authorization headers used by this provider.
     *
     * Typically this is "Bearer" or "MAC". For more information see:
     * http://tools.ietf.org/html/rfc6749#section-7.1
     *
     * No default is provided, providers must overload this method to activate
     * authorization headers.
     *
     * @param  mixed|null $token Either a string or an access token instance
     * @return array
     */
    public function getAuthorizationHeaders($token = null): array
    {
        return array('X-Shopify-Access-Token' => $token->getToken());
    }
}
