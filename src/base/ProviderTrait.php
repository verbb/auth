<?php
namespace verbb\auth\base;

use verbb\auth\Auth;
use verbb\auth\helpers\UrlHelper as AuthUrlHelper;
use verbb\auth\models\Token;

use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;

use Throwable;

use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\AccessToken as OAuth2Token;

trait ProviderTrait
{
    // Abstract Methods
    // =========================================================================
    
    abstract public function getBaseApiUrl(): ?string;


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

    public function getApiRequestQueryParams(Token $token): array
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
        $accessToken = $token->getToken();

        try {
            // Does the provider have an expires value, and is it expired?
            if ($force || ($accessToken->getExpires() && $accessToken->hasExpired())) {
                // Get the provider to generate a new refresh token from the current one
                $newAccessToken = $this->getRefreshToken($accessToken);

                if ($newAccessToken) {
                    // Update the database token
                    Auth::$plugin->getTokens()->refreshToken($token, $newAccessToken);

                    return $token;
                }
            }
        } catch (Throwable $e) {
            Auth::error('Unable to refresh token for “{provider}”: “{message}” {file}:{line}', [
                'provider' => get_class($this),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return $token;
    }

    public function getApiRequest(string $method = 'GET', string $uri = '', Token $token, array $options = [], bool $forceRefresh = true): mixed
    {
        try {
            // Normalise the URL and query params
            $baseUri = ArrayHelper::remove($options, 'base_uri', $this->getBaseApiUrl());
            $baseUri = AuthUrlHelper::normalizeBaseUri($baseUri);

            // For cases where we want to pass in an absolute URL
            $url = UrlHelper::isAbsoluteUrl($uri) ? $uri : $baseUri . '/' . ltrim($uri, '/');
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

            // Normalise passing in `form_params` or `json`, like Guzzle normally would
            if ($json = ArrayHelper::remove($options, 'json')) {
                $options['body'] = Json::encode($json);
                $options['headers']['Content-Type'] = 'application/json';
            }

            if ($formParams = ArrayHelper::remove($options, 'form_params')) {
                $options['body'] = http_build_query($formParams, '', '&');
                $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
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
            Auth::error('An error was thrown for an API request for “{provider}”: “{message}” {file}:{line}', [
                'provider' => get_class($this),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // If this has failed as unauthorized, assume it's because the token needs refreshing
            if ($e->getCode() === 401 && $forceRefresh) {
                // Force-refresh the token
                $this->refreshToken($token, true);

                // Then try again, with the new access token
                return $this->getApiRequest($method, $uri, $token, $options, false);
            }

            // Otherwise, throw the error as normal to allow plugins upstream to handle it
            throw $e;
        }
    }
}
