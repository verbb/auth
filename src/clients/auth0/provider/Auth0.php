<?php
namespace verbb\auth\clients\auth0\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\auth0\provider\exception\AccountNotProvidedException;
use verbb\auth\clients\auth0\provider\exception\Auth0IdentityProviderException;
use verbb\auth\clients\auth0\provider\exception\InvalidRegionException;

class Auth0 extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const REGION_US = 'us';
    public const REGION_EU = 'eu';
    public const REGION_AU = 'au';
    public const REGION_JP = 'jp';

    protected array $availableRegions = [self::REGION_US, self::REGION_EU, self::REGION_AU, self::REGION_JP];

    protected string $region = self::REGION_US;

    protected $account;

    protected $customDomain;

    protected function domain(): string
    {
        if ($this->customDomain !== null) {
            return $this->customDomain;
        }

        if (empty($this->account)) {
            throw new AccountNotProvidedException();
        }
        if (!in_array($this->region, $this->availableRegions)) {
            throw new InvalidRegionException();
        }

        $domain = 'auth0.com';

        if ($this->region !== self::REGION_US) {
            $domain = $this->region . '.' . $domain;
        }

        return $this->account . '.' . $domain;
    }

    protected function baseUrl(): string
    {
        return 'https://' . $this->domain();
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->baseUrl() . '/authorize';
    }

    public function getBaseAccessTokenUrl(array $params = []): string
    {
        return $this->baseUrl() . '/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->baseUrl() . '/userinfo';
    }

    public function getDefaultScopes(): array
    {
        return ['openid', 'profile', 'email'];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw Auth0IdentityProviderException::fromResponse(
                $response,
                $data['error'] ?: $response->getReasonPhrase()
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): Auth0ResourceOwner
    {
        return new Auth0ResourceOwner($response);
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }
}
