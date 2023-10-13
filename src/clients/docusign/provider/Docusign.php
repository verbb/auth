<?php

namespace verbb\auth\clients\docusign\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Docusign extends AbstractProvider
{
    use BearerAuthorizationTrait {
        getAuthorizationHeaders as getTokenBearerAuthorizationHeaders;
    }

    public const URL_ROOT = 'https://account.docusign.com/oauth';
    public const URL_ROOT_SANDBOX = 'https://account-d.docusign.com/oauth';

    public const ENDPOINT_AUTHORIZTION = 'auth';
    public const ENDPOINT_ACCESS_TOKEN = 'token';
    public const ENDPOINT_RESOURCE_OWNER_DETAILS = 'userinfo';

    public const SCOPE_SIGNATURE = 'signature';
    public const SCOPE_EXTENDED = 'extended';
    public const SCOPE_IMPERSONATION = 'impersonation';
    public const SCOPES_DEFAULT = [
        self::SCOPE_SIGNATURE,
        self::SCOPE_EXTENDED
    ];
    public const SCOPES_SEPARATOR = ' ';

    protected bool $sandbox = false;

    public function getBaseAuthorizationUrl(): string
    {
        return $this->getUrl(self::ENDPOINT_AUTHORIZTION);
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getUrl(self::ENDPOINT_ACCESS_TOKEN);
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getUrl(self::ENDPOINT_RESOURCE_OWNER_DETAILS);
    }

    /**
     * Returns a full url for the given path, with the appropriate docusign
     * backennd.
     *
     * It can be used in combination of getRequest and getResponse methods
     * to make further calls to docusign endpoint using the given token.
     *
     * @param string $path
     *
     * @return string
     *
     * @see Docusign::getRequest
     * @see Docusign::getResponse
     */
    public function getUrl(string $path): string
    {
        return sprintf(
            '%s/%s',
            $this->sandbox ? self::URL_ROOT_SANDBOX : self::URL_ROOT,
            $path
        );
    }

    protected function getDefaultScopes(): array
    {
        return self::SCOPES_DEFAULT;
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): DocusignUser
    {
        return new DocusignUser($response, $token);
    }

    protected function getScopeSeparator(): string
    {
        return self::SCOPES_SEPARATOR;
    }

    protected function getDefaultHeaders(): array
    {
        return ['Authorization' => 'Basic ' . $this->getBasicAuth()];
    }

    private function getBasicAuth(): string
    {
        return base64_encode(sprintf('%s:%s', $this->clientId, $this->clientSecret));
    }
}
