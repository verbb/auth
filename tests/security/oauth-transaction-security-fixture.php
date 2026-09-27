<?php
namespace verbb\auth {
    class Auth
    {
        public static mixed $instance;

        public static function getInstance(): mixed
        {
            return self::$instance;
        }
    }
}

namespace {
    $vendorDir = getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__, 2) . '/vendor';

    require $vendorDir . '/autoload.php';
    require $vendorDir . '/yiisoft/yii2/Yii.php';
    require $vendorDir . '/craftcms/cms/src/Craft.php';
    require __DIR__ . '/../../src/base/OAuthProviderInterface.php';
    require __DIR__ . '/../../src/helpers/Session.php';
    require __DIR__ . '/../../src/services/OAuthTransactions.php';

    use verbb\auth\base\OAuthProviderInterface;
    use verbb\auth\helpers\Session;
    use verbb\auth\services\OAuthTransactions;
    use yii\caching\ArrayCache;
    use yii\web\Cookie;
    use yii\web\CookieCollection;
    use yii\web\Response;

    class FixtureCache extends ArrayCache
    {
        public bool $failDelete = false;

        protected function deleteValue($key)
        {
            if ($this->failDelete) {
                return false;
            }

            return parent::deleteValue($key);
        }
    }

    class FixtureProvider implements OAuthProviderInterface
    {
        public function __construct(
            private string $state,
            private string $callbackUri = 'https://site.test/callback',
            private string $grant = 'authorization_code',
        ) {
        }

        public static function getOAuthProviderClass(): string
        {
            return self::class;
        }

        public function getRedirectUri(): string
        {
            return $this->callbackUri;
        }

        public function getGrant(): string
        {
            return $this->grant;
        }

        public function getOAuthTransactionData(): array
        {
            return [
                'state' => $this->state,
                'transactionKey' => $this->state,
                'origin' => 'https://site.test/start',
                'redirect' => 'https://site.test/done',
            ];
        }
    }

    class LegacyFixtureProvider implements OAuthProviderInterface
    {
        public static function getOAuthProviderClass(): string
        {
            return self::class;
        }

        public function getRedirectUri(): string
        {
            return 'https://site.test/callback';
        }

        public function getGrant(): string
        {
            return 'authorization_code';
        }
    }

    class FixtureRequest extends yii\web\Request
    {
        public array $fixtureQueryParams = [];
        public array $fixtureBodyParams = [];
        public string $fixtureHostInfo = 'https://site.test';
        public CookieCollection $fixtureCookies;

        public function __construct()
        {
            parent::__construct();
            $this->fixtureCookies = new CookieCollection();
        }

        public function getQueryParams(): array
        {
            return $this->fixtureQueryParams;
        }

        public function getBodyParams(): array
        {
            return $this->fixtureBodyParams;
        }

        public function getHostInfo(): string
        {
            return $this->fixtureHostInfo;
        }

        public function getIsSecureConnection(): bool
        {
            return str_starts_with($this->fixtureHostInfo, 'https://');
        }

        public function getCookies(): CookieCollection
        {
            return $this->fixtureCookies;
        }

        public function getParam(string $name, mixed $defaultValue = null): mixed
        {
            return $this->fixtureQueryParams[$name] ?? $this->fixtureBodyParams[$name] ?? $defaultValue;
        }
    }

    class FixtureSession extends ArrayObject
    {
        public function get(string $key): mixed
        {
            return $this[$key] ?? null;
        }

        public function set(string $key, mixed $value): void
        {
            $this[$key] = $value;
        }

        public function remove(string $key): void
        {
            unset($this[$key]);
        }
    }

    class FixtureMutex
    {
        public function acquire(string $name, int $timeout): bool
        {
            return true;
        }

        public function release(string $name): bool
        {
            return true;
        }
    }

    class FixtureConfig
    {
        public function getGeneral(): object
        {
            return (object)[
                'useSecureCookies' => true,
                'defaultCookieDomain' => '',
                'sameSiteCookieValue' => Cookie::SAME_SITE_LAX,
                'pathParam' => 'p',
            ];
        }
    }

    class FixtureUser
    {
        public ?int $id = 42;

        public function getId(): ?int
        {
            return $this->id;
        }
    }

    class FixtureStoredUser
    {
        public bool $admin = false;
        public bool $locked = false;
        public bool $passwordResetRequired = false;
        public string $status = 'active';
        public array $permissions = ['manage-integrations'];

        public function getStatus(): string
        {
            return $this->status;
        }

        public function can(string $permission): bool
        {
            return $this->admin || in_array($permission, $this->permissions, true);
        }
    }

    class FixtureUsers
    {
        public ?FixtureStoredUser $storedUser;

        public function __construct()
        {
            $this->storedUser = new FixtureStoredUser();
        }

        public function getUserById(int $userId): ?FixtureStoredUser
        {
            return $userId === 42 ? $this->storedUser : null;
        }
    }

    class FixtureEndException extends RuntimeException
    {
    }

    class FixtureApp
    {
        public string $charset = 'UTF-8';
        public FixtureCache $cache;
        public FixtureRequest $request;
        public Response $response;
        public FixtureSession $session;
        public FixtureUser $user;
        public FixtureUsers $users;

        public function __construct()
        {
            Craft::$app = $this;
            Yii::$app = $this;
            $this->cache = new FixtureCache();
            $this->request = new FixtureRequest();
            $this->response = new Response();
            $this->session = new FixtureSession();
            $this->user = new FixtureUser();
            $this->users = new FixtureUsers();
        }

        public function getCache(): FixtureCache
        {
            return $this->cache;
        }

        public function getRequest(): FixtureRequest
        {
            return $this->request;
        }

        public function getResponse(): Response
        {
            return $this->response;
        }

        public function getSession(): FixtureSession
        {
            return $this->session;
        }

        public function getSecurity(): yii\base\Security
        {
            return new yii\base\Security();
        }

        public function getMutex(): FixtureMutex
        {
            return new FixtureMutex();
        }

        public function getConfig(): FixtureConfig
        {
            return new FixtureConfig();
        }

        public function getUser(): FixtureUser
        {
            return $this->user;
        }

        public function getUsers(): FixtureUsers
        {
            return $this->users;
        }

        public function end(int $status = 0, ?Response $response = null): never
        {
            throw new FixtureEndException('', $status);
        }
    }

    function check(string $label, bool $condition): void
    {
        if (!$condition) {
            throw new RuntimeException("Failed: {$label}");
        }

        echo "{$label}: PASS\n";
    }

    function resetResponse(FixtureApp $app): void
    {
        $app->response = new Response();
    }

    function copyResponseCookie(FixtureApp $app, string $state): void
    {
        $cookieName = 'verbb_auth_oauth_' . substr(hash('sha256', $state), 0, 32);
        $cookie = $app->response->getCookies()->get($cookieName);
        $app->request->fixtureCookies = new CookieCollection();
        $app->request->fixtureCookies->add(new Cookie([
            'name' => $cookieName,
            'value' => $cookie->value,
        ]));
    }

    $app = new FixtureApp();
    Craft::$app = $app;
    Yii::$app = $app;
    $transactions = new OAuthTransactions();
    \verbb\auth\Auth::$instance = new class($transactions) {
        public function __construct(private OAuthTransactions $transactions)
        {
        }

        public function getOAuth(): object
        {
            return new class($this->transactions) {
                public function __construct(private OAuthTransactions $transactions)
                {
                }

                public function prepareCallback(?string $ownerHandle = null): ?Response
                {
                    return $this->transactions->prepare($ownerHandle);
                }

                public function claimCallback(?string $ownerHandle = null, ?string $transactionId = null): array
                {
                    return $this->transactions->claim($ownerHandle, $transactionId);
                }
            };
        }
    };

    $sameBrowserState = 'same-browser-state';
    $response = $transactions->begin('fixture', new FixtureProvider($sameBrowserState), 'target-a', ['target' => 'alpha'], 'https://provider.test/authorize');
    check('Same-origin initiation redirects to the provider', $response->getHeaders()->get('location') === 'https://provider.test/authorize');
    copyResponseCookie($app, $sameBrowserState);
    resetResponse($app);
    $app->request->fixtureQueryParams = ['state' => $sameBrowserState];
    $transaction = $transactions->claim('fixture');
    check('Same browser can claim its transaction', $transaction['reference'] === 'target-a');
    check('Explicit context is restored after a valid claim', $app->session->get('verbb-auth.target') === 'alpha');

    $replayed = false;
    try {
        $transactions->claim('fixture');
    } catch (Throwable) {
        $replayed = true;
    }
    check('A claimed transaction cannot be replayed', $replayed);

    $deleteFailureState = 'delete-failure-state';
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider($deleteFailureState), 'delete-failure', ['deleteFailure' => 'must-not-restore'], 'https://provider.test/authorize');
    copyResponseCookie($app, $deleteFailureState);
    $app->request->fixtureQueryParams = ['state' => $deleteFailureState];
    $app->cache->failDelete = true;
    $deleteFailureRejected = false;
    try {
        $transactions->claim('fixture');
    } catch (Throwable) {
        $deleteFailureRejected = true;
    }
    check('Cache deletion failure aborts the claim', $deleteFailureRejected && $app->session->get('verbb-auth.deleteFailure') === null);
    $app->cache->failDelete = false;

    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider('tab-a-state'), 'tab-a', ['tab' => 'a'], 'https://provider.test/authorize');
    $tabACookieName = 'verbb_auth_oauth_' . substr(hash('sha256', 'tab-a-state'), 0, 32);
    $tabACookie = $app->response->getCookies()->get($tabACookieName);
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider('tab-b-state'), 'tab-b', ['tab' => 'b'], 'https://provider.test/authorize');
    $tabBCookieName = 'verbb_auth_oauth_' . substr(hash('sha256', 'tab-b-state'), 0, 32);
    $tabBCookie = $app->response->getCookies()->get($tabBCookieName);
    $app->request->fixtureCookies = new CookieCollection();
    $app->request->fixtureCookies->add(new Cookie(['name' => $tabACookieName, 'value' => $tabACookie->value]));
    $app->request->fixtureCookies->add(new Cookie(['name' => $tabBCookieName, 'value' => $tabBCookie->value]));
    $app->request->fixtureQueryParams = ['state' => 'tab-a-state'];
    check('First concurrent tab keeps its own context', $transactions->claim('fixture')['reference'] === 'tab-a');
    $app->request->fixtureQueryParams = ['state' => 'tab-b-state'];
    check('Second concurrent tab keeps its own context', $transactions->claim('fixture')['reference'] === 'tab-b');

    $differentBrowserState = 'different-browser-state';
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider($differentBrowserState), 'target-b', [], 'https://provider.test/authorize');
    $app->request->fixtureCookies = new CookieCollection();
    $app->request->fixtureQueryParams = ['state' => $differentBrowserState];
    $differentBrowserRejected = false;
    try {
        $transactions->claim('fixture');
    } catch (Throwable) {
        $differentBrowserRejected = true;
    }
    check('A different browser cannot claim the transaction', $differentBrowserRejected);

    $duplicateState = 'duplicate-state';
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider($duplicateState), 'target-c', [], 'https://provider.test/authorize');
    copyResponseCookie($app, $duplicateState);
    $app->request->fixtureQueryParams = ['state' => $duplicateState];
    $app->request->fixtureBodyParams = ['state' => $duplicateState];
    $duplicateRejected = false;
    try {
        $transactions->claim('fixture');
    } catch (Throwable) {
        $duplicateRejected = true;
    }
    check('Duplicate query and body state values are rejected', $duplicateRejected);

    $appleState = 'apple-post-state';
    resetResponse($app);
    $app->request->fixtureBodyParams = [];
    $transactions->begin('fixture', new FixtureProvider($appleState), 'target-d', [], 'https://provider.test/authorize');
    copyResponseCookie($app, $appleState);
    resetResponse($app);
    $app->request->fixtureQueryParams = [];
    $app->request->fixtureBodyParams = ['state' => $appleState];
    check('Apple-style POST state is accepted for the initiating browser', $transactions->claim('fixture')['id'] === $appleState);

    $crossHostState = 'cross-host-state';
    resetResponse($app);
    $app->request->fixtureHostInfo = 'https://admin.test';
    $app->request->fixtureBodyParams = [];
    $response = $transactions->begin('fixture', new FixtureProvider($crossHostState), 'target-e', [], 'https://provider.test/authorize');
    check('Cross-host initiation first redirects to the callback host', str_starts_with($response->getHeaders()->get('location'), 'https://site.test/callback?verbbAuthPrepare='));
    resetResponse($app);
    $app->request->fixtureHostInfo = 'https://site.test';
    $app->request->fixtureQueryParams = [OAuthTransactions::PREPARE_PARAM => $crossHostState];
    $response = $transactions->prepare('fixture');
    check('Cross-host preparation continues to the provider', $response?->getHeaders()->get('location') === 'https://provider.test/authorize');
    copyResponseCookie($app, $crossHostState);
    resetResponse($app);
    $app->request->fixtureQueryParams = ['state' => $crossHostState];
    check('Cross-host callback keeps browser continuity without a PHP session', $transactions->claim('fixture')['id'] === $crossHostState);

    $legacyCrossHostState = 'legacy-cross-host-state';
    resetResponse($app);
    $app->request->fixtureHostInfo = 'https://admin.test';
    $app->request->fixtureQueryParams = [];
    $response = $transactions->begin('legacy-owner', new FixtureProvider($legacyCrossHostState), null, ['legacyCrossHost' => 'restored'], 'https://provider.test/authorize');
    check('Legacy cross-host initiation first redirects to the callback host', str_starts_with($response->getHeaders()->get('location'), 'https://site.test/callback?verbbAuthPrepare='));
    resetResponse($app);
    $app->request->fixtureHostInfo = 'https://site.test';
    $app->request->fixtureQueryParams = [OAuthTransactions::PREPARE_PARAM => $legacyCrossHostState];
    $legacyPrepareEnded = false;
    try {
        Session::restoreSession(null);
    } catch (FixtureEndException) {
        $legacyPrepareEnded = true;
    }
    check('Legacy session restoration sends the cross-host preparation redirect', $legacyPrepareEnded && $app->response->getHeaders()->get('location') === 'https://provider.test/authorize');
    copyResponseCookie($app, $legacyCrossHostState);
    resetResponse($app);
    $app->request->fixtureQueryParams = ['state' => $legacyCrossHostState];
    Session::restoreSession($legacyCrossHostState);
    check('Legacy cross-host callbacks restore their transaction context', Session::get('legacyCrossHost') === 'restored');

    $ownerState = 'owner-bound-state';
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider($ownerState), 'target-f', [], 'https://provider.test/authorize');
    copyResponseCookie($app, $ownerState);
    $app->request->fixtureQueryParams = ['state' => $ownerState];
    $wrongOwnerRejected = false;
    try {
        $transactions->claim('another-owner');
    } catch (Throwable) {
        $wrongOwnerRejected = true;
    }
    check('A different consumer cannot claim the transaction', $wrongOwnerRejected);

    $actorState = 'actor-bound-state';
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider($actorState), 'target-actor', [], 'https://provider.test/authorize');
    copyResponseCookie($app, $actorState);
    $app->user->id = 43;
    $app->request->fixtureQueryParams = ['state' => $actorState];
    $wrongActorRejected = false;
    try {
        $transactions->claim('fixture');
    } catch (Throwable) {
        $wrongActorRejected = true;
    }
    check('A different signed-in Craft user cannot claim the transaction', $wrongActorRejected);
    $app->user->id = 42;

    $authorizedActorState = 'authorized-actor-state';
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider($authorizedActorState), 'target-authorized-actor', [], 'https://provider.test/authorize');
    copyResponseCookie($app, $authorizedActorState);
    $app->user->id = null;
    $app->request->fixtureQueryParams = ['state' => $authorizedActorState];
    $transaction = $transactions->claimAuthorized('fixture', fn(FixtureStoredUser $user): bool => $user->can('manage-integrations'));
    check('An active permitted initiator can finish without a PHP login session', $transaction['initiatingUserId'] === 42);

    $revokedActorState = 'revoked-actor-state';
    $app->user->id = 42;
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider($revokedActorState), 'target-revoked-actor', [], 'https://provider.test/authorize');
    copyResponseCookie($app, $revokedActorState);
    $app->user->id = null;
    $app->users->storedUser->permissions = [];
    $app->request->fixtureQueryParams = ['state' => $revokedActorState];
    $revokedActorRejected = false;
    try {
        $transactions->claimAuthorized('fixture', fn(FixtureStoredUser $user): bool => $user->can('manage-integrations'));
    } catch (Throwable) {
        $revokedActorRejected = true;
    }
    $revokedCacheKey = 'verbb-auth.oauth-transaction.' . hash('sha256', $revokedActorState);
    check('A callback is rejected and consumed after its initiator loses permission', $revokedActorRejected && $app->cache->get($revokedCacheKey) === false);

    foreach (['suspended', 'locked', 'password-reset', 'deleted'] as $invalidActorState) {
        $state = "invalid-actor-{$invalidActorState}";
        $app->user->id = 42;
        $app->users->storedUser = new FixtureStoredUser();
        resetResponse($app);
        $transactions->begin('fixture', new FixtureProvider($state), "target-{$invalidActorState}", [], 'https://provider.test/authorize');
        copyResponseCookie($app, $state);
        $app->user->id = null;

        if ($invalidActorState === 'suspended') {
            $app->users->storedUser->status = 'suspended';
        } elseif ($invalidActorState === 'locked') {
            $app->users->storedUser->locked = true;
        } elseif ($invalidActorState === 'password-reset') {
            $app->users->storedUser->passwordResetRequired = true;
        } else {
            $app->users->storedUser = null;
        }

        $app->request->fixtureQueryParams = ['state' => $state];
        $invalidActorRejected = false;
        try {
            $transactions->claimAuthorized('fixture', fn(FixtureStoredUser $user): bool => $user->can('manage-integrations'));
        } catch (Throwable) {
            $invalidActorRejected = true;
        }
        check("A {$invalidActorState} initiator cannot finish an OAuth callback", $invalidActorRejected);
    }

    $app->users->storedUser = new FixtureStoredUser();
    $app->user->id = 42;

    $missingInitiatorState = 'missing-initiator-state';
    $app->user->id = null;
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider($missingInitiatorState), 'target-missing-initiator', [], 'https://provider.test/authorize');
    copyResponseCookie($app, $missingInitiatorState);
    $app->request->fixtureQueryParams = ['state' => $missingInitiatorState];
    $missingInitiatorRejected = false;
    try {
        $transactions->claimAuthorized('fixture', fn(FixtureStoredUser $user): bool => $user->can('manage-integrations'));
    } catch (Throwable) {
        $missingInitiatorRejected = true;
    }
    check('An OAuth transaction without an initiating user cannot use the authorized callback path', $missingInitiatorRejected);
    $app->user->id = 42;

    $expiredState = 'expired-state';
    resetResponse($app);
    $transactions->begin('fixture', new FixtureProvider($expiredState), 'target-expired', [], 'https://provider.test/authorize');
    copyResponseCookie($app, $expiredState);
    $expiredCacheKey = 'verbb-auth.oauth-transaction.' . hash('sha256', $expiredState);
    $expiredTransaction = $app->cache->get($expiredCacheKey);
    $expiredTransaction['expiresAt'] = time() - 1;
    $app->cache->set($expiredCacheKey, $expiredTransaction, 60);
    $app->request->fixtureQueryParams = ['state' => $expiredState];
    $expiredRejected = false;
    try {
        $transactions->claim('fixture');
    } catch (Throwable) {
        $expiredRejected = true;
    }
    check('Expired transactions are rejected', $expiredRejected);

    foreach ([null, '', ['unexpected']] as $invalidState) {
        $app->request->fixtureBodyParams = [];
        $app->request->fixtureQueryParams = $invalidState === null ? [] : ['state' => $invalidState];
        $invalidRejected = false;
        try {
            $transactions->claim('fixture');
        } catch (Throwable) {
            $invalidRejected = true;
        }
        check('Missing, empty, or non-scalar callback state is rejected', $invalidRejected);
    }

    $oauth1State = 'oauth1-temporary-token';
    resetResponse($app);
    $app->request->fixtureQueryParams = [];
    $transactions->begin('fixture', new FixtureProvider($oauth1State, 'https://site.test/callback', 'oauth1'), 'target-g', [], 'https://provider.test/authorize');
    copyResponseCookie($app, $oauth1State);
    resetResponse($app);
    $app->request->fixtureQueryParams = ['oauth_token' => $oauth1State, 'oauth_verifier' => 'verifier'];
    check('OAuth 1 callback uses its temporary token as the transaction key', $transactions->claim('fixture')['id'] === $oauth1State);

    resetResponse($app);
    $app->request->fixtureQueryParams = [];
    $response = $transactions->begin('fixture', new FixtureProvider('unused-provider-state', 'https://site.test/callback', 'client_credentials'), 'target-h', [], null);
    parse_str(parse_url($response->getHeaders()->get('location'), PHP_URL_QUERY), $clientCredentialsQuery);
    $clientCredentialsId = $clientCredentialsQuery[OAuthTransactions::CALLBACK_PARAM] ?? null;
    check('Client credentials uses a private local transaction identifier', is_string($clientCredentialsId) && $clientCredentialsId !== 'unused-provider-state');
    copyResponseCookie($app, $clientCredentialsId);
    resetResponse($app);
    $app->request->fixtureQueryParams = [OAuthTransactions::CALLBACK_PARAM => $clientCredentialsId];
    check('Client credentials callback does not require provider state', $transactions->claim('fixture')['grant'] === 'client_credentials');

    $proxyState = 'proxy-return-state';
    resetResponse($app);
    $app->request->fixtureHostInfo = 'https://admin.test';
    $app->request->fixtureQueryParams = [];
    $proxyCallback = 'https://proxy.verbb.io?return=' . rawurlencode('http://local.test/callback');
    $response = $transactions->begin('fixture', new FixtureProvider($proxyState, $proxyCallback), 'target-i', [], 'https://provider.test/authorize');
    check('Proxy callbacks prepare the returned-to application host', $response->getHeaders()->get('location') === 'http://local.test/callback?verbbAuthPrepare=proxy-return-state');

    resetResponse($app);
    $app->request->fixtureHostInfo = 'https://site.test';
    $app->request->fixtureQueryParams = [];
    Session::set('legacyHandle', 'legacy-provider');
    $transactions->begin('legacy-owner', new LegacyFixtureProvider(), 'legacy-target', [], 'https://provider.test/authorize?state=legacy-state');
    copyResponseCookie($app, 'legacy-state');
    Session::remove('legacyHandle');
    resetResponse($app);
    $app->request->fixtureQueryParams = ['state' => 'legacy-state'];
    $transactions->claim('legacy-owner');
    check('Legacy interface providers derive state and restore explicit session context', Session::get('legacyHandle') === 'legacy-provider');
}
