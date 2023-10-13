<?php

declare(strict_types=1);

namespace verbb\auth\clients\deezer\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use verbb\auth\clients\deezer\provider\exception\DeezerIdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use function array_key_exists;
use UnexpectedValueException;

class Deezer extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    public const BASE_DEEZER_URL = 'https://connect.deezer.com/oauth/';
    public const RESPONSE_TYPE = 'code';

    // Available scopes.
    public const SCOPE_BASIC_ACCESS = 'basic_access';
    public const SCOPE_EMAIL = 'email';
    public const SCOPE_OFFLINE_ACCESS = 'offline_access';
    public const SCOPE_MANAGE_LIBRARY = 'manage_library';
    public const SCOPE_MANAGE_COMMUNITY = 'manage_community';
    public const SCOPE_DELETE_LIBRARY = 'delete_library';
    public const SCOPE_LISTENING_HISTORY = 'listening_history';

    public function getBaseAuthorizationUrl(): string
    {
        return self::BASE_DEEZER_URL . 'auth.php';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return self::BASE_DEEZER_URL . 'access_token.php';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.deezer.com/user/me?' . http_build_query(['access_token' => $token->getToken()]);
    }

    protected function getDefaultScopes(): array
    {
        return [
            self::SCOPE_BASIC_ACCESS,
        ];
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        // handle HTTP Status errors
        if ($response->getStatusCode() >= 500) {
            throw new DeezerIdentityProviderException('Server Error: ' . $response->getReasonPhrase(), $response->getStatusCode(), (string) $response->getBody());
        }

        if ($response->getStatusCode() >= 400) {
            throw new DeezerIdentityProviderException('Client Error: ' . $response->getReasonPhrase(), $response->getStatusCode(), (string) $response->getBody());
        }

        // Skip error checking if we get what we expected
        if (array_key_exists('access_token', $data)) {
            return;
        }

        // handle errors in 'error' key
        if (!empty($data['error'])) {
            $message = match ($data['error']['type']) {
                'DataException' => 'No data returned.',
                default => $data['error']['type'] . ': ' . $data['error']['message'],
            };

            throw new DeezerIdentityProviderException($message, 0, $data);
        }

        // handle errors NOT in 'error' key
        if (array_key_exists('wrong_code', $data)) {
            throw new DeezerIdentityProviderException('Wrong code.', 0, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new DeezerResourceOwner($response);
    }

    protected function parseResponse(ResponseInterface $response): array|string
    {
        $content = (string) $response->getBody();
        $type = $this->getContentType($response);
        if (mb_strpos($type, 'urlencoded') !== false || mb_strpos($type, 'text/html') !== false) {
            parse_str($content, $parsed);

            return $parsed;
        }

        // Attempt to parse the string as JSON regardless of content type,
        // since some providers use non-standard content types. Only throw an
        // exception if the JSON could not be parsed when it was expected to.
        try {
            return $this->parseJson($content);
        } catch (UnexpectedValueException $e) {
            if (mb_strpos($type, 'json') !== false) {
                throw $e;
            }

            return $content; // @phpstan-ignore-line Abstract method defined return type array, but in their implementation it could also return string
        }
    }

    protected function prepareAccessTokenResponse(array $result): array
    {
        $result = parent::prepareAccessTokenResponse($result);
        if (array_key_exists('expires', $result) && '0' === $result['expires']) {
            // the token never expires. set a very long time expiring token because we need to set it
            $result['expires_in'] = 50 * 31556952;
            unset($result['expires']);
        }

        return $result;
    }
}
