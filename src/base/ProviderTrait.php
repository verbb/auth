<?php
namespace verbb\auth\base;

use verbb\auth\Auth;
use verbb\auth\models\Token;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;

use Throwable;

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

    public function getRefreshToken(OAuth2Token $accessToken)
    {
        $refreshToken = $accessToken->getRefreshToken();

        if ($refreshToken) {
            return $this->getAccessToken('refresh_token', [
                'refresh_token' => $refreshToken,
            ]);
        }

        return null;
    }

    public function refreshToken(Token $token, bool $force = false): ?OAuth2Token
    {
        $accessToken = $token->getToken();

        try {
            if ($force || ($accessToken->getExpires() && $accessToken->hasExpired())) {
                $newAccessToken = $this->getRefreshToken($accessToken);

                if ($newAccessToken) {
                    Auth::$plugin->getTokens()->refreshToken($token, $newAccessToken);

                    return $newAccessToken;
                }
            }
        } catch (Throwable $e) {

        }

        return $accessToken;
    }

    public function getApiRequest(string $method, string $uri, Token $token, array $options = []): mixed
    {
        // Normalise the URL
        $url = rtrim($this->getBaseApiUrl(), '/') . '/' . ltrim($uri, '/');
        $params = $this->getApiRequestQueryParams($token);

        if ($query = ArrayHelper::remove($options, 'query')) {
            $params = array_merge($params, $query);
        }

        $url = UrlHelper::urlWithParams($url, $params);

        $accessToken = $this->refreshToken($token);

        $request = $this->getAuthenticatedRequest($method, $url, $accessToken, $options);

        return $this->getParsedResponse($request);
    }
}
