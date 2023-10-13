<?php

declare(strict_types=1);

namespace verbb\auth\clients\buddy\provider;

use verbb\auth\clients\buddy\provider\exception\BuddyIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Buddy extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const SCOPE_WORKSPACE = 'WORKSPACE';
    public const SCOPE_PROJECT_DELETE = 'PROJECT_DELETE';
    public const SCOPE_REPOSITORY_READ = 'REPOSITORY_READ';
    public const SCOPE_REPOSITORY_WRITE = 'REPOSITORY_WRITE';
    public const SCOPE_EXECUTION_INFO = 'EXECUTION_INFO';
    public const SCOPE_EXECUTION_RUN = 'EXECUTION_RUN';
    public const SCOPE_EXECUTION_MANAGE = 'EXECUTION_MANAGE';
    public const SCOPE_USER_INFO = 'USER_INFO';
    public const SCOPE_USER_KEY = 'USER_KEY';
    public const SCOPE_USER_EMAIL = 'USER_EMAIL';
    public const SCOPE_INTEGRATION_INFO = 'INTEGRATION_INFO';
    public const SCOPE_MEMBER_EMAIL = 'MEMBER_EMAIL';
    public const SCOPE_MANAGE_EMAILS = 'MANAGE_EMAILS';
    public const SCOPE_WEBHOOK_INFO = 'WEBHOOK_INFO';
    public const SCOPE_WEBHOOK_ADD = 'WEBHOOK_ADD';
    public const SCOPE_WEBHOOK_MANAGE = 'WEBHOOK_MANAGE';
    public const SCOPE_VARIABLE_ADD = 'VARIABLE_ADD';
    public const SCOPE_VARIABLE_INFO = 'VARIABLE_INFO';
    public const SCOPE_VARIABLE_MANAGE = 'VARIABLE_MANAGE';

    private const SCOPE_SEPARATOR = ' '; // will be encoded as + (Buddy use + as scope separator)

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
        $this->baseUrl = rtrim($options['baseApiUrl'] ?? 'https://api.buddy.works', '/');
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->baseUrl.'/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->baseUrl.'/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->baseUrl.'/user';
    }

    /**
     * @return string[]
     */
    protected function getDefaultScopes(): array
    {
        return [self::SCOPE_USER_INFO];
    }

    /**
     * @param array $data
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400 || isset($data['errors'])) {
            throw BuddyIdentityProviderException::clientException($response, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): BuddyResourceOwner
    {
        return new BuddyResourceOwner($response);
    }

    protected function getScopeSeparator(): string
    {
        return self::SCOPE_SEPARATOR;
    }
}
