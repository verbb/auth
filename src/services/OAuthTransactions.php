<?php
namespace verbb\auth\services;

use verbb\auth\base\OAuthProviderInterface;
use verbb\auth\helpers\Session;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;

use yii\web\BadRequestHttpException;
use yii\web\Cookie;
use yii\web\Response;

class OAuthTransactions extends Component
{
    // Constants
    // =========================================================================

    public const DURATION = 900;
    public const PREPARE_PARAM = 'verbbAuthPrepare';
    public const CALLBACK_PARAM = 'verbbAuthCallback';


    // Public Methods
    // =========================================================================

    public function begin(string $ownerHandle, OAuthProviderInterface $provider, string|int|null $reference, array $context, ?string $authorizationUrl): Response
    {
        $providerData = method_exists($provider, 'getOAuthTransactionData') ? $provider->getOAuthTransactionData() : [];
        $grant = method_exists($provider, 'getGrant') ? $provider->getGrant() : 'authorization_code';
        $providerData = $this->_normalizeProviderData($providerData, $authorizationUrl);
        $transactionId = $providerData['transactionKey'] ?? null;

        if ($context === []) {
            $context = Session::getAll();
        }

        if ($grant === 'client_credentials') {
            $transactionId = Craft::$app->getSecurity()->generateRandomString(48);
        }

        if (!is_string($transactionId) || $transactionId === '') {
            throw new BadRequestHttpException('Unable to create an OAuth transaction.');
        }

        $callbackUri = $provider->getRedirectUri();

        if (!is_string($callbackUri) || $callbackUri === '') {
            throw new BadRequestHttpException('Unable to create an OAuth transaction without a callback URL.');
        }

        $now = time();
        $browserCallbackUri = $this->_browserCallbackUri($callbackUri);
        $transaction = [
            'id' => $transactionId,
            'ownerHandle' => $ownerHandle,
            'providerClass' => get_class($provider),
            'reference' => $reference === null ? null : (string)$reference,
            'context' => $context,
            'providerData' => $providerData,
            'callbackUri' => $callbackUri,
            'browserCallbackUri' => $browserCallbackUri,
            'authorizationUrl' => $authorizationUrl,
            'grant' => $grant,
            'initiatingUserId' => Craft::$app->getUser()->getId(),
            'createdAt' => $now,
            'expiresAt' => $now + self::DURATION,
            'prepared' => false,
            'browserHash' => null,
        ];

        if ($this->_hasSameOrigin($browserCallbackUri)) {
            $browserSecret = $this->_bindTransaction($transaction);
            $this->_store($transaction);
            $this->_issueCookie($transaction, $browserSecret);

            return Craft::$app->getResponse()->redirect($this->_authorizationTarget($transaction));
        }

        $this->_store($transaction);

        return Craft::$app->getResponse()->redirect(UrlHelper::urlWithParams($browserCallbackUri, [
            self::PREPARE_PARAM => $transactionId,
        ]));
    }

    public function prepare(?string $ownerHandle = null): ?Response
    {
        $transactionId = $this->_queryValue(self::PREPARE_PARAM);

        if ($transactionId === null) {
            return null;
        }

        $mutexName = $this->_mutexName($transactionId);
        $mutex = Craft::$app->getMutex();

        if (!$mutex->acquire($mutexName, 5)) {
            throw new BadRequestHttpException('Unable to prepare the OAuth transaction.');
        }

        try {
            $transaction = $this->_get($transactionId);
            $this->_validateTransaction($transaction, $ownerHandle);

            if ($transaction['prepared']) {
                throw new BadRequestHttpException('The OAuth transaction has already been prepared.');
            }

            $browserSecret = $this->_bindTransaction($transaction);
            $this->_store($transaction);
        } finally {
            $mutex->release($mutexName);
        }

        $this->_issueCookie($transaction, $browserSecret);

        return Craft::$app->getResponse()->redirect($this->_authorizationTarget($transaction));
    }

    public function claim(?string $ownerHandle = null, ?string $transactionId = null): array
    {
        $transactionId ??= $this->_callbackTransactionId();
        $mutexName = $this->_mutexName($transactionId);
        $mutex = Craft::$app->getMutex();

        if (!$mutex->acquire($mutexName, 5)) {
            throw new BadRequestHttpException('Unable to claim the OAuth transaction.');
        }

        try {
            $transaction = $this->_get($transactionId);
            $this->_validateTransaction($transaction, $ownerHandle);

            if (!$transaction['prepared'] || !is_string($transaction['browserHash']) || $transaction['browserHash'] === '') {
                throw new BadRequestHttpException('The OAuth transaction was not prepared.');
            }

            $browserSecret = Craft::$app->getRequest()->getCookies()->getValue($this->_cookieName($transactionId));

            if (!is_string($browserSecret) || $browserSecret === '' || !hash_equals($transaction['browserHash'], hash('sha256', $browserSecret))) {
                throw new BadRequestHttpException('The OAuth transaction belongs to a different browser.');
            }

            if (!Craft::$app->getCache()->delete($this->_cacheKey($transactionId))) {
                throw new BadRequestHttpException('Unable to consume the OAuth transaction.');
            }
        } finally {
            $mutex->release($mutexName);
        }

        $this->_clearCookie($transactionId);
        $this->_restoreContext($transaction);

        return $transaction;
    }


    // Private Methods
    // =========================================================================

    private function _authorizationTarget(array $transaction): string
    {
        if ($transaction['grant'] === 'client_credentials') {
            return UrlHelper::urlWithParams($transaction['callbackUri'], [
                self::CALLBACK_PARAM => $transaction['id'],
            ]);
        }

        if (!is_string($transaction['authorizationUrl']) || $transaction['authorizationUrl'] === '') {
            throw new BadRequestHttpException('Unable to find the OAuth authorization URL.');
        }

        return $transaction['authorizationUrl'];
    }

    private function _bindTransaction(array &$transaction): string
    {
        $browserSecret = Craft::$app->getSecurity()->generateRandomString(64);
        $transaction['prepared'] = true;
        $transaction['browserHash'] = hash('sha256', $browserSecret);

        return $browserSecret;
    }

    private function _store(array $transaction): void
    {
        $duration = max(1, $transaction['expiresAt'] - time());

        if (!Craft::$app->getCache()->set($this->_cacheKey($transaction['id']), $transaction, $duration)) {
            throw new BadRequestHttpException('Unable to store the OAuth transaction.');
        }
    }

    private function _get(string $transactionId): array
    {
        $transaction = Craft::$app->getCache()->get($this->_cacheKey($transactionId));

        if (!is_array($transaction)) {
            throw new BadRequestHttpException('The OAuth transaction is invalid or has expired.');
        }

        return $transaction;
    }

    private function _validateTransaction(array $transaction, ?string $ownerHandle): void
    {
        if (($transaction['expiresAt'] ?? 0) < time()) {
            Craft::$app->getCache()->delete($this->_cacheKey($transaction['id'] ?? ''));

            throw new BadRequestHttpException('The OAuth transaction has expired.');
        }

        if ($ownerHandle !== null && !hash_equals($transaction['ownerHandle'] ?? '', $ownerHandle)) {
            throw new BadRequestHttpException('The OAuth transaction belongs to a different owner.');
        }

        $initiatingUserId = $transaction['initiatingUserId'] ?? null;
        $currentUserId = Craft::$app->getUser()->getId();

        if ($initiatingUserId !== null && $currentUserId !== null && (string)$initiatingUserId !== (string)$currentUserId) {
            throw new BadRequestHttpException('The OAuth transaction was started by a different Craft user.');
        }
    }

    private function _restoreContext(array $transaction): void
    {
        foreach (['redirect', 'origin'] as $key) {
            if (array_key_exists($key, $transaction['providerData'])) {
                Session::set($key, $transaction['providerData'][$key]);
            }
        }

        foreach (['state', 'pkceCode', 'pkceVerifier', 'oauth2verifier', 'temporaryCredentials'] as $key) {
            if (array_key_exists($key, $transaction['providerData'])) {
                Session::set($key, $transaction['providerData'][$key]);
            }
        }

        foreach ($transaction['context'] as $key => $value) {
            if (is_string($key) && $key !== '') {
                Session::set($key, $value);
            }
        }
    }

    private function _callbackTransactionId(): string
    {
        if (($transactionId = $this->_queryValue(self::CALLBACK_PARAM)) !== null) {
            return $transactionId;
        }

        if (($state = $this->_requestValue('state')) !== null) {
            return $state;
        }

        if (($oauthToken = $this->_requestValue('oauth_token')) !== null) {
            return $oauthToken;
        }

        throw new BadRequestHttpException('The OAuth callback is missing its transaction identifier.');
    }

    private function _normalizeProviderData(array $providerData, ?string $authorizationUrl): array
    {
        $sessionData = Session::getAll();

        foreach (['redirect', 'origin', 'state', 'pkceCode', 'pkceVerifier', 'oauth2verifier', 'temporaryCredentials'] as $key) {
            if (!array_key_exists($key, $providerData) && array_key_exists($key, $sessionData)) {
                $providerData[$key] = $sessionData[$key];
            }
        }

        if (!isset($providerData['transactionKey']) && is_string($authorizationUrl)) {
            $query = parse_url($authorizationUrl, PHP_URL_QUERY);

            if (is_string($query)) {
                parse_str($query, $params);

                foreach (['state', 'oauth_token'] as $key) {
                    if (isset($params[$key]) && is_string($params[$key]) && $params[$key] !== '') {
                        $providerData['transactionKey'] = $params[$key];

                        if ($key === 'state') {
                            $providerData['state'] = $params[$key];
                        }

                        break;
                    }
                }
            }
        }

        $providerData['transactionKey'] ??= $providerData['state'] ?? null;

        return $providerData;
    }

    private function _queryValue(string $name): ?string
    {
        $queryParams = Craft::$app->getRequest()->getQueryParams();

        if (!array_key_exists($name, $queryParams)) {
            return null;
        }

        $value = $queryParams[$name];

        if (!is_string($value) || $value === '') {
            throw new BadRequestHttpException('The OAuth transaction identifier is invalid.');
        }

        return $value;
    }

    private function _requestValue(string $name): ?string
    {
        $request = Craft::$app->getRequest();
        $queryParams = $request->getQueryParams();
        $bodyParams = $request->getBodyParams();
        $hasQueryValue = array_key_exists($name, $queryParams);
        $hasBodyValue = array_key_exists($name, $bodyParams);

        if ($hasQueryValue && $hasBodyValue) {
            throw new BadRequestHttpException("The OAuth callback contains duplicate {$name} values.");
        }

        if (!$hasQueryValue && !$hasBodyValue) {
            return null;
        }

        $value = $hasQueryValue ? $queryParams[$name] : $bodyParams[$name];

        if (!is_string($value) || $value === '') {
            throw new BadRequestHttpException("The OAuth callback contains an invalid {$name} value.");
        }

        return $value;
    }

    private function _hasSameOrigin(string $url): bool
    {
        $callback = parse_url($url);
        $current = parse_url(Craft::$app->getRequest()->getHostInfo());

        if (!is_array($callback) || !is_array($current)) {
            return false;
        }

        return strtolower($callback['scheme'] ?? '') === strtolower($current['scheme'] ?? '') &&
            strtolower($callback['host'] ?? '') === strtolower($current['host'] ?? '') &&
            ($callback['port'] ?? null) === ($current['port'] ?? null);
    }

    private function _browserCallbackUri(string $callbackUri): string
    {
        $query = parse_url($callbackUri, PHP_URL_QUERY);

        if (is_string($query)) {
            parse_str($query, $params);
            $returnUrl = $params['return'] ?? null;

            if (is_string($returnUrl) && preg_match('/^https?:\/\//i', $returnUrl)) {
                return $returnUrl;
            }
        }

        return $callbackUri;
    }

    private function _issueCookie(array $transaction, string $browserSecret): void
    {
        $request = Craft::$app->getRequest();
        $cookieConfig = Craft::cookieConfig([], $request);
        $secure = $request->getIsSecureConnection() || ($cookieConfig['secure'] ?? false);
        $cookie = new Cookie(array_merge($cookieConfig, [
            'name' => $this->_cookieName($transaction['id']),
            'value' => $browserSecret,
            'expire' => $transaction['expiresAt'],
            'secure' => $secure,
            'sameSite' => $secure ? Cookie::SAME_SITE_NONE : Cookie::SAME_SITE_LAX,
        ]));

        Craft::$app->getResponse()->getCookies()->add($cookie);
    }

    private function _clearCookie(string $transactionId): void
    {
        Craft::$app->getResponse()->getCookies()->remove(new Cookie(Craft::cookieConfig([
            'name' => $this->_cookieName($transactionId),
        ], Craft::$app->getRequest())));
    }

    private function _cookieName(string $transactionId): string
    {
        return 'verbb_auth_oauth_' . substr(hash('sha256', $transactionId), 0, 32);
    }

    private function _cacheKey(string $transactionId): string
    {
        return 'verbb-auth.oauth-transaction.' . hash('sha256', $transactionId);
    }

    private function _mutexName(string $transactionId): string
    {
        return 'verbb-auth.oauth-transaction:' . hash('sha256', $transactionId);
    }
}
