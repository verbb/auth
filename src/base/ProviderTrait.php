<?php
namespace verbb\auth\base;

use verbb\auth\Auth;
use verbb\auth\events\TokenEvent;
use verbb\auth\exceptions\OAuthTokenRefreshException;
use verbb\auth\helpers\UrlHelper as AuthUrlHelper;
use verbb\auth\models\Token;
use verbb\auth\services\Tokens;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;

use Closure;
use Throwable;

use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\AccessToken as OAuth2Token;

trait ProviderTrait
{
    // Abstract Methods
    // =========================================================================
    
    abstract public function getBaseApiUrl(?Token $token): ?string;


    // Properties
    // =========================================================================

    public Closure|string|null $baseApiUrl = null;


    // Public Methods
    // =========================================================================

    public function getGrant(): string
    {
        return 'authorization_code';
    }

    public function defaultScopes(): array
    {
        // Open up the default protected `getDefaultScopes()` function
        return $this->getDefaultScopes();
    }

    public function getResolvedBaseApiUrl(?Token $token): ?string
    {
        // We should use this function to allow either overriding the baseApiUrl via config
        // or when the includer of this trait provides a `getBaseApiUrl()` function.
        if ($this->baseApiUrl) {
            if (is_string($this->baseApiUrl)) {
                return $this->baseApiUrl;
            }

            if (is_callable($this->baseApiUrl)) {
                return ($this->baseApiUrl)($token);
            }
        }

        return $this->getBaseApiUrl($token);
    }

    public function getApiRequestQueryParams(?Token $token): array
    {
        return [];
    }

    public function getRefreshToken(OAuth2Token $accessToken): AccessTokenInterface|OAuth2Token|null
    {
        $refreshToken = $accessToken->getRefreshToken();

        if ($refreshToken) {
            return $this->getAccessToken('refresh_token', [
                'refresh_token' => $refreshToken,
            ]);
        }

        return null;
    }

    public function refreshToken(Token $token, bool $force = false): ?Token
    {
        $token = $this->_reloadToken($token);

        if (!$this->_tokenNeedsRefresh($token, $force)) {
            return $token;
        }

        $mutexName = $this->_getRefreshMutexName($token);

        if (!$mutexName) {
            return $this->_performTokenRefresh($token, $force);
        }

        $mutex = Craft::$app->getMutex();

        if (!$mutex->acquire($mutexName, 30)) {
            Auth::error('Unable to acquire token refresh lock for “{provider}” (token #{id}). Reloading token from the database.', [
                'provider' => get_class($this),
                'id' => $token->id,
            ]);

            return $this->_reloadToken($token);
        }

        try {
            $token = $this->_reloadToken($token);

            if (!$this->_tokenNeedsRefresh($token, $force)) {
                return $token;
            }

            return $this->_performTokenRefresh($token, $force);
        } finally {
            $mutex->release($mutexName);
        }
    }

    public function getApiRequest(string $method = 'GET', string $uri = '', ?Token $token, array $options = [], bool $forceRefresh = true): mixed
    {
        $originalOptions = $options;

        try {
            // Normalise the URL and query params
            $baseUri = ArrayHelper::remove($options, 'base_uri', $this->getResolvedBaseApiUrl($token));
            $baseUri = rtrim($baseUri, '/');

            // For cases where we want to pass in an absolute URL
            $url = UrlHelper::isAbsoluteUrl($uri) || !$baseUri ? $uri : $baseUri . '/' . ltrim($uri, '/');
            $params = $this->getApiRequestQueryParams($token);

            if ($query = ArrayHelper::remove($options, 'query')) {
                $params = array_merge($params, $query);
            }

            $url = UrlHelper::urlWithParams($url, $params);

            // Check if the token needs to be refreshed. This isn't the most reliable thing for most
            // providers, as it seems in practice the expiry on tokens is largely ignored, or just
            // not used. But let's do best practices anyway!
            $token = $this->refreshToken($token);
            $accessToken = $token->getToken();

            // Normalise passing in `form_params`, `json` or `multipart`, like Guzzle normally would
            if ($json = ArrayHelper::remove($options, 'json')) {
                $options['body'] = Json::encode($json);
                $options['headers']['Content-Type'] = 'application/json';
            }

            if ($formParams = ArrayHelper::remove($options, 'form_params')) {
                $options['body'] = http_build_query($formParams, '', '&');
                $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
            }

            if ($multipart = ArrayHelper::remove($options, 'multipart')) {
                $options['body'] =  $multipart;
                $boundary = ArrayHelper::remove($options, 'boundary');
                $options['headers']['Content-Type'] = 'multipart/form-data; boundary=' . $boundary;
            }

            // Perform the actual request
            $request = $this->getAuthenticatedRequest($method, $url, $accessToken, $options);

            // Don't use `getParsedResponse()`, some providers wrap their own exceptions that replace Guzzle `BadResponseException`
            // exceptions which has all the good bits like status codes. Instead, we want that exception thrown to be handled upstream.
            // And of course, so they're properly handled by providers, still call `parseResponse()` and `checkResponse()`.
            $response = $this->getResponse($request);
            $parsed = $this->parseResponse($response);

            $this->checkResponse($response, $parsed);

            return $parsed;
        } catch (Throwable $e) {
            // Refresh already failed permanently — do not retry the API with a deleted token.
            if ($e instanceof OAuthTokenRefreshException) {
                throw $e;
            }

            Auth::error('An error was thrown for an API request for “{provider}”: “{message}” {file}:{line}', [
                'provider' => get_class($this),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // If this has failed as unauthorized, assume it's because the token needs refreshing
            if ($e->getCode() === 401 && $forceRefresh) {
                $failedAccessToken = $token->accessToken ?? null;
                $token = $this->_reloadToken($token);

                // Another process may have already refreshed the token. Compare access token
                // strings — do not use expiry metadata here. Providers like Salesforce often omit
                // `expires_in`, so `_tokenNeedsRefresh($token, false)` would always be false and
                // we'd skip the refresh, then retry with the same dead token.
                if ($failedAccessToken && $token->accessToken && $token->accessToken !== $failedAccessToken) {
                    return $this->getApiRequest($method, $uri, $token, $originalOptions, false);
                }

                $this->refreshToken($token, true);

                // Reload again in case another process refreshed while we were waiting on the lock
                $token = $this->_reloadToken($token);

                // Then try again, with the latest access token
                return $this->getApiRequest($method, $uri, $token, $originalOptions, false);
            }

            // Otherwise, throw the error as normal to allow plugins upstream to handle it
            throw $e;
        }
    }


    // Private Methods
    // =========================================================================

    private function _reloadToken(?Token $token): ?Token
    {
        if (!$token) {
            return null;
        }

        $tokens = Auth::getInstance()->getTokens();

        if ($token->id) {
            return $tokens->getTokenById($token->id) ?? $token;
        }

        if ($token->ownerHandle && $token->reference) {
            return $tokens->getTokenByOwnerReference($token->ownerHandle, $token->reference) ?? $token;
        }

        return $token;
    }

    private function _getRefreshMutexName(Token $token): ?string
    {
        if ($token->id) {
            return 'auth-token-refresh-' . $token->id;
        }

        if ($token->ownerHandle && $token->reference) {
            return 'auth-token-refresh-' . $token->ownerHandle . '-' . $token->reference;
        }

        return null;
    }

    private function _tokenNeedsRefresh(Token $token, bool $force): bool
    {
        $accessToken = $token->getToken();

        if (!$accessToken instanceof OAuth2Token) {
            return false;
        }

        return $force || ($accessToken->getExpires() && $accessToken->hasExpired());
    }

    private function _performTokenRefresh(Token $token, bool $force): ?Token
    {
        $accessToken = $token->getToken();

        try {
            if ($force || ($accessToken->getExpires() && $accessToken->hasExpired())) {
                $newAccessToken = $this->getRefreshToken($accessToken);

                if ($newAccessToken) {
                    Auth::getInstance()->getTokens()->refreshToken($token, $newAccessToken);

                    return $token;
                }
            }
        } catch (Throwable $e) {
            $message = $e->getMessage();

            Auth::error('Unable to refresh token for “{provider}” (token #{id}): “{message}” {file}:{line}', [
                'provider' => get_class($this),
                'id' => $token->id,
                'message' => $message,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Permanent rejection (revoked, Testing-mode 7-day expiry, rotated, etc.) —
            // drop the stored token so isConnected()-style checks flip and callers reconnect.
            if ($this->_isInvalidGrant($message)) {
                Auth::error('Token refresh failed with invalid_grant for “{provider}” (token #{id}). The refresh token is no longer valid; deleting stored token so the owner can reconnect.', [
                    'provider' => get_class($this),
                    'id' => $token->id,
                ]);

                $tokens = Auth::getInstance()->getTokens();

                if ($tokens->hasEventHandlers(Tokens::EVENT_TOKEN_REFRESH_FAILED)) {
                    $tokens->trigger(Tokens::EVENT_TOKEN_REFRESH_FAILED, new TokenEvent([
                        'token' => $token,
                        'exception' => $e,
                    ]));
                }

                $tokens->deleteToken($token);

                throw new OAuthTokenRefreshException(
                    'OAuth refresh token is no longer valid. Reconnect the integration to continue.',
                    (int)$e->getCode(),
                    $e
                );
            }
        }

        return $this->_reloadToken($token);
    }

    private function _isInvalidGrant(string $message): bool
    {
        $normalized = strtolower($message);

        return str_contains($normalized, 'invalid_grant')
            || str_contains($normalized, 'token has been expired or revoked');
    }
}
