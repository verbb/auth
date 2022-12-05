<?php
namespace verbb\auth\base;

use verbb\auth\models\Token;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;

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

    public function getApiRequest(string $method, string $uri, Token $token, array $options = [])
    {
        // Normalise the URL
        $url = rtrim($this->getBaseApiUrl(), '/') . '/' . ltrim($uri, '/');
        $params = $this->getApiRequestQueryParams($token);

        if ($query = ArrayHelper::remove($options, 'query')) {
            $params = array_merge($params, $query);
        }

        $url = UrlHelper::urlWithParams($url, $params);

        $request = $this->getAuthenticatedRequest($method, $url, $token->getToken(), $options);

        return $this->getParsedResponse($request);
    }
}
