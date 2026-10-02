<?php
namespace verbb\auth\base;

use verbb\auth\Auth;
use verbb\auth\helpers\Redirect;
use verbb\auth\models\Token;

use Craft;
use craft\helpers\App;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;

use Exception;

use League\OAuth1\Client\Credentials\TokenCredentials as OAuth1Token;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Server\Server as OAuth1Provider;
use League\OAuth2\Client\Provider\AbstractProvider as OAuth2Provider;
use League\OAuth2\Client\Token\AccessToken as OAuth2Token;

use GuzzleHttp\Client;

use function GuzzleHttp\default_user_agent;

trait OAuthProviderTrait
{
    // Properties
    // =========================================================================

    public array $config = [];
    public ?string $clientId = null;
    public ?string $clientSecret = null;
    public ?string $redirectUri = null;
    public array $scopes = [];
    public string $scopeSeparator = ' ';

    protected OAuth1Provider|OAuth2Provider|null $_oauthProvider = null;
    protected array $_oauthTransactionData = [];


    // Public Methods
    // =========================================================================

    public function getClientId(): ?string
    {
        return App::parseEnv($this->clientId);
    }

    public function getClientSecret(): ?string
    {
        return App::parseEnv($this->clientSecret);
    }

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->getOAuthProvider()->getBaseApiUrl($token);
    }

    public function getOAuthVersion(): int
    {
        // Determine the OAuth version based on the string class, because we check against
        // the OAuth version when passing in the init config, which will throw us in a loop.
        $className = static::getOAuthProviderClass();
        $isOAuth1 = is_subclass_of($className, OAuth1Provider::class) || is_a($className, OAuth1Provider::class, true);

        return ($isOAuth1) ? 1 : 2;
    }

    public function getIsOAuth1(): bool
    {
        return $this->getOAuthVersion() === 1;
    }

    public function getIsOAuth2(): bool
    {
        return $this->getOAuthVersion() === 2;
    }

    public function getOAuthProviderConfig(): array
    {
        if ($this->getIsOAuth1()) {
            return array_merge([
                'identifier' => $this->getClientId(),
                'secret' => $this->getClientSecret(),
                'callback_uri' => $this->getRedirectUri(),
            ], $this->config);
        }

        return array_merge([
            'clientId' => $this->getClientId(),
            'clientSecret' => $this->getClientSecret(),
            'redirectUri' => $this->getRedirectUri(),
        ], $this->config);
    }

    public function getOAuthProvider(): OAuth1Provider|OAuth2Provider
    {
        if ($this->_oauthProvider !== null) {
            return $this->_oauthProvider;
        }

        $className = static::getOAuthProviderClass();

        return $this->_oauthProvider = new $className($this->getOAuthProviderConfig());
    }

    public function getAuthorizationUrlOptions(): array
    {
        return [];
    }

    public function getAuthorizationUrl(): ?string
    {
        $authUrl = null;
        $request = Craft::$app->getRequest();
        $oauthProvider = $this->getOAuthProvider();

        // Signed redirects are an explicit application choice. An unsigned referrer must stay on the initiating origin.
        $redirect = $request->getParam('redirect');
        $redirect = is_string($redirect) ? Redirect::allowed(Craft::$app->getSecurity()->validateData($redirect)) : null;
        $origin = Redirect::safeReferrer($request->getReferrer());
        $this->_oauthTransactionData = [
            'redirect' => $redirect ?? $origin,
            'origin' => $origin,
        ];

        // OAuth v1
        if ($this->getIsOAuth1()) {
            $temporaryCredentials = $oauthProvider->getTemporaryCredentials();

            $this->_oauthTransactionData['temporaryCredentials'] = [
                'identifier' => $temporaryCredentials->getIdentifier(),
                'secret' => $temporaryCredentials->getSecret(),
            ];
            $this->_oauthTransactionData['transactionKey'] = $temporaryCredentials->getIdentifier();

            $authUrl = $oauthProvider->getAuthorizationUrl($temporaryCredentials);
        }

        // OAuth v2
        if ($this->getIsOAuth2()) {
            // Must do this before calling `getState()`
            $authUrl = $oauthProvider->getAuthorizationUrl($this->getAuthorizationUrlOptions());

            $this->_oauthTransactionData['state'] = $oauthProvider->getState();
            $this->_oauthTransactionData['transactionKey'] = $oauthProvider->getState();

            // Persist League OAuth2 PKCE verifier across the redirect when enabled
            if (method_exists($oauthProvider, 'getPkceCode') && ($pkceCode = $oauthProvider->getPkceCode())) {
                $this->_oauthTransactionData['pkceCode'] = $pkceCode;
            }

            if (method_exists($oauthProvider, 'getPkceVerifier')) {
                $this->_oauthTransactionData['pkceVerifier'] = $oauthProvider->getPkceVerifier();
            }
        }

        return $authUrl;
    }

    public function getAccessTokenOptions(array $options = []): array
    {
        return $options;
    }

    public function getOAuthTransactionData(): array
    {
        return $this->_oauthTransactionData;
    }

    public function setOAuthTransactionData(array $data): void
    {
        $this->_oauthTransactionData = $data;
    }

    public function getAccessToken(): OAuth1Token|OAuth2Token|null
    {
        $accessToken = null;
        $request = Craft::$app->getRequest();
        $oauthProvider = $this->getOAuthProvider();

        // OAuth v1
        if ($this->getIsOAuth1()) {
            $oauthToken = $request->getParam('oauth_token');
            $oauthVerifier = $request->getParam('oauth_verifier');
            $temporaryCredentialsData = $this->_oauthTransactionData['temporaryCredentials'] ?? null;

            if (!is_string($oauthToken) || $oauthToken === '' || !is_string($oauthVerifier) || $oauthVerifier === '' || !is_array($temporaryCredentialsData)) {
                throw new Exception('Invalid OAuth 1 callback.');
            }

            if (!hash_equals((string)($temporaryCredentialsData['identifier'] ?? ''), $oauthToken)) {
                throw new Exception('Invalid OAuth 1 callback token.');
            }

            $temporaryCredentials = new TemporaryCredentials();
            $temporaryCredentials->setIdentifier($temporaryCredentialsData['identifier'] ?? '');
            $temporaryCredentials->setSecret($temporaryCredentialsData['secret'] ?? '');

            // Obtain token credentials from the server.
            $accessToken = $oauthProvider->getTokenCredentials($temporaryCredentials, $oauthToken, $oauthVerifier);
        }

        // OAuth v2
        if ($this->getIsOAuth2()) {
            // Check for error
            $error = $request->getParam('error_code');

            if ($error) {
                if (is_array($error)) {
                    $error = Json::encode($error);
                }

                Auth::error('An error occurred: {error}.', $error);

                throw new Exception('An error occurred.');
            }

            $grant = $this->getGrant();
            $code = $request->getParam('code');
            $state = $request->getParam('state');
            $transactionState = $this->_oauthTransactionData['state'] ?? null;

            if ($grant === 'authorization_code') {
                if (!is_string($state) || $state === '' || !is_string($transactionState) || $transactionState === '' || !hash_equals($transactionState, $state)) {
                    Auth::error('Invalid callback state. State is mismatched: {state} - {transactionState}.', ['state' => $state, 'transactionState' => $transactionState]);

                    throw new Exception('Invalid callback state. State is mismatched.');
                }

                if (!is_string($code) || $code === '') {
                    throw new Exception('Invalid callback authorization code.');
                }

                // Restore League OAuth2 PKCE verifier for the token exchange
                if (method_exists($oauthProvider, 'setPkceCode') && ($pkceCode = $this->_oauthTransactionData['pkceCode'] ?? null)) {
                    $oauthProvider->setPkceCode($pkceCode);
                }

                if (method_exists($oauthProvider, 'setPkceVerifier') && ($pkceVerifier = $this->_oauthTransactionData['pkceVerifier'] ?? null)) {
                    $oauthProvider->setPkceVerifier($pkceVerifier);
                }

                $accessToken = $oauthProvider->getAccessToken($grant, $this->getAccessTokenOptions([
                    'code' => $code,
                ]));
            } elseif ($grant === 'client_credentials') {
                $options = [];

                if (!empty($this->scopes)) {
                    $options['scope'] = $this->scopes;
                }

                $accessToken = $oauthProvider->getAccessToken($grant, $this->getAccessTokenOptions($options));
            }

            // Some providers (Facebook, Instagram) have long-lived tokens, so use those
            if (method_exists($oauthProvider, 'getLongLivedAccessToken')) {
                $accessToken = $oauthProvider->getLongLivedAccessToken((string)$accessToken);
            }
        }

        return $accessToken;
    }

    public function getToken(): ?Token
    {
        return null;
    }

    public function getGrant(): string
    {
        if ($this->getIsOAuth1()) {
            return 'oauth1';
        }

        return $this->getOAuthProvider()->getGrant();
    }

    public function beforeFetchAccessToken(): void
    {
        return;
    }

    public function afterFetchAccessToken(Token $token): void
    {
        return;
    }

    public function getRequestOptions(Token $token): array
    {
        return [];
    }

    public function request(string $method = 'GET', string $uri = '', array $options = [])
    {
        $oauthProvider = $this->getOAuthProvider();
        $token = $this->getToken();

        // Check if `client_credentials` grant - a new token should be fetched each time
        // TODO: handle this a little better...
        if ($this->getGrant() === 'client_credentials') {
            $tokenOptions = [];

            if (!empty($this->scopes)) {
                $tokenOptions['scope'] = $this->scopes;
            }

            $accessToken = $oauthProvider->getAccessToken('client_credentials', $this->getAccessTokenOptions($tokenOptions));

            // Keep response-specific values such as Salesforce's `instance_url` available while
            // ensuring the per-request token remains detached from the persisted token record.
            $token = Auth::getInstance()->getTokens()->createToken(
                $token?->ownerHandle ?? '',
                $this,
                $accessToken,
            );
        }

        if (!$token) {
            throw new Exception('Unable to make an OAuth request without a stored token. Reconnect the integration and try again.');
        }

        // Ensure that the prepped Guzzle client is used
        $oauthProvider->setHttpClient($this->getClient());

        // Allow providers to modify the options for a request
        $options = array_merge($this->getRequestOptions($token), $options);

        return $oauthProvider->getApiRequest($method, $uri, $token, $options);
    }

    public function getClient(): Client
    {
        $oauthProvider = $this->getOAuthProvider();
        $token = $this->getToken();

        // Combine the provider and Craft's Guzzle clients. We can't alter Guzzle config after the fact
        $config = ArrayHelper::merge($oauthProvider->getHttpClient()->getConfig(), $this->_getGuzzleConfig());

        // Allow implementers to override the `baseApiUrl` for a provider for convenience
        if ($baseUrl = $this->getBaseApiUrl($token)) {
            $config['base_uri'] = $baseUrl;
        }

        return new Client($config);
    }


    // Private Methods
    // =========================================================================

    private function _getGuzzleConfig(): array
    {
        // Set the Craft header by default.
        $defaultConfig = [
            'headers' => [
                'User-Agent' => 'Craft/' . Craft::$app->getVersion() . ' ' . default_user_agent(),
            ],
        ];

        // Grab the config from config/guzzle.php that is used on every Guzzle request.
        $configService = Craft::$app->getConfig();
        $guzzleConfig = $configService->getConfigFromFile('guzzle');
        $generalConfig = $configService->getGeneral();

        // Merge everything together
        $guzzleConfig = ArrayHelper::merge($defaultConfig, $guzzleConfig);

        if ($generalConfig->httpProxy) {
            $guzzleConfig['proxy'] = $generalConfig->httpProxy;
        }

        return $guzzleConfig;
    }
}
