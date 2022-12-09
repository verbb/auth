<?php
namespace verbb\auth\base;

use verbb\auth\Auth;
use verbb\auth\models\Token;

use craft\helpers\ArrayHelper;
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

    public function getApiRequest(string $method, string $uri, Token $token, array $options = [], bool $forceRefresh = true): mixed
    {
        try {
            // Normalise the URL and query params
            $url = rtrim($this->getBaseApiUrl(), '/') . '/' . ltrim($uri, '/');
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

            // Perform the actual request
            $request = $this->getAuthenticatedRequest($method, $url, $accessToken, $options);

            return $this->getParsedResponse($request);
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
        }

        return [];
    }
}