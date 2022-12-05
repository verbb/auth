<?php

namespace verbb\auth\clients\square\provider;

use verbb\auth\clients\square\grant\RenewToken;

use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Grant\Exception\InvalidGrantException;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Square extends AbstractProvider
{
    use BearerAuthorizationTrait;

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'merchant_id';

    /**
     * Enable debugging by connecting to the Square staging server.
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * Get a Square connect URL, depending on path.
     *
     * @param  string $path
     * @return string
     */
    protected function getConnectUrl($path)
    {
        $staging = $this->debug ? 'staging' : '';
        return "https://connect.squareup{$staging}.com/{$path}";
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->getConnectUrl('oauth2/authorize');
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getConnectUrl('oauth2/token');
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getConnectUrl('v1/me');
    }

    /**
     * Get the URL for rewnewing an access token.
     *
     * Square does not provide normal refresh tokens, and provides token
     * renewal instead.
     *
     * @param  array $params
     * @return string
     */
    public function getBaseRenewTokenUrl(array $params)
    {
        return $this->getConnectUrl(sprintf(
            'oauth2/clients/%s/access-token/renew',
            $this->clientId
        ));
    }

    public function setGrantFactory(GrantFactory $factory)
    {
        // Register the renew token as a possible grant, rather than overloading
        // getAccessToken to support it.
        try {
            $factory->getGrant('renew_token');
        } catch (InvalidGrantException $e) {
            $factory->setGrant('renew_token', new RenewToken);
        }

        return parent::setGrantFactory($factory);
    }

    protected function verifyGrant($grant)
    {
        $grant = parent::verifyGrant($grant);

        if ($grant instanceof RefreshToken) {
            throw new InvalidGrantException('Refresh tokens are not supported by Square');
        }

        return $grant;
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function getDefaultScopes()
    {
        return [
            'MERCHANT_PROFILE_READ',
        ];
    }

    protected function getDefaultHeaders()
    {
        return array_merge(parent::getDefaultHeaders(), [
            'Accept' => 'application/json',
        ]);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['type']) && $response->getStatusCode() >= 400) {
            throw new IdentityProviderException($data['message'], 0, $data);
        }
    }

    protected function prepareAccessTokenResponse(array $result)
    {
        // Square uses a ISO 8601 timestamp to represent the expiration date.
        // http://docs.connect.squareup.com/#post-token
        $result['expires_in'] = strtotime($result['expires_at']) - time();

        return parent::prepareAccessTokenResponse($result);
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new SquareMerchant($response);
    }
}
