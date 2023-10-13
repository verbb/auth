<?php

namespace verbb\auth\clients\reddit\provider;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use verbb\auth\clients\reddit\grant\InstalledClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class Reddit extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * User agent string required by Reddit
     * Format <platform>:<app ID>:<version string> (by /u/<reddit username>)
     *
     * @see https://github.com/reddit/reddit/wiki/API
     */
    public string $userAgent = "";

    /**
     * {}
     */
    public string $authorizationHeader = "bearer";

    public function getBaseAuthorizationUrl(): string
    {
        return "https://ssl.reddit.com/api/v1/authorize";
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return "https://ssl.reddit.com/api/v1/access_token";
    }

    // AbstractProvider::getBaseAuthorizationUrl, League\OAuth2\Client\Provider\AbstractProvider::getBaseAccessTokenUrl

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return "https://oauth.reddit.com/api/v1/me";
    }

    public function getDefaultScopes(): array
    {
        return ['identity', 'read'];
    }

    public function createResourceOwner(array $response, AccessToken $token): array|ResourceOwnerInterface
    {
        return $response;
    }

    private function parseErrorMessage($data): string
    {
        if (isset($data['error_description'])) {
            return $data['error_description'];
        }

        return $data['message'] ?? $data['error'] ?? 'Unknown error';
    }

    public function checkResponse(Response $response, $data): void
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $this->parseErrorMessage($data),
                $response->getStatusCode(),
                $response);
        }
    }

    /**
     * Returns the user agent, which is required to be set.
     *
     * @return string
     * @throws Rudolf\OAuth2\Client\Exception\ProviderException
     */
    protected function getUserAgent(): string
    {
        if ($this->userAgent) {
            return $this->userAgent;
        }

        // Use the server user agent as a fallback if no explicit one was set.
        return $_SERVER["HTTP_USER_AGENT"];
    }


    /**
     * Validates that the user agent follows the Reddit API guide.
     * Pattern: <platform>:<app ID>:<version string> (by /u/<reddit username>)
     *
     * @throws Rudolf\OAuth2\Client\Exception\ProviderException
     */
    protected function validateUserAgent(): void
    {
        if ( ! preg_match("~^.+:.+:.+ \(by /u/.+\)$~", $this->getUserAgent())) {
            throw new InvalidArgumentException("User agent is not valid");
        }
    }

    public function getHeaders($token = null): array
    {
        $this->validateUserAgent();

        $headers = [
            "User-Agent" => $this->getUserAgent(),
        ];

        // We have to use HTTP Basic Auth when requesting an access token
        if ( ! $token) {
            $auth = base64_encode("{$this->clientId}:{$this->clientSecret}");
            $headers["Authorization"] = "Basic $auth";
        }

        return array_merge(parent::getHeaders($token), $headers);
    }

    /**
     * {@inheritDoc}
     *
     * @see https://github.com/reddit/reddit/wiki/OAuth2
     */
    public function getAccessToken($grant, array $options = []): AccessTokenInterface|AccessToken
    {
        // Allow Reddit-specific 'installed_client' to be specified as a string,
        // keeping consistent with the other grant types.
        if ($grant === "installed_client") {
            $grant = new InstalledClient();
        }

        return parent::getAccessToken($grant, $options);
    }

    public function getAuthorizationUrl(array $options = []): string
    {
        $url = parent::getAuthorizationUrl($options);

        // This is required as an option to be given a refresh token
        if (isset($options["duration"])) {
            $url .= "&duration={$options['duration']}";
        }

        return $url;
    }
}
