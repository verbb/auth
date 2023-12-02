<?php 

namespace verbb\auth\clients\sugarcrm\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Sugarcrm extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected $url;

    /**
     * @var string Key used in a token response to identify the resource owner.
     */
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    public function getBaseAuthorizationUrl(): string
    {
        return $this->url . '/rest/v11/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->url . '/rest/v11/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->url . '/rest/v11/metadata';
    }

    protected function getDefaultScopes(): array
    {
        return [];
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            throw new IdentityProviderException(
                $data['description'] ?? $response->getReasonPhrase(),
                $statusCode,
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): SugarcrmResourceOwner
    {
        return new SugarcrmResourceOwner($response);
    }
}
