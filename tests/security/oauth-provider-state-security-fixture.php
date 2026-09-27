<?php
namespace verbb\auth {
    class Auth
    {
        public static function error(...$args): void
        {
        }
    }
}

namespace {
    $vendorDir = getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__, 2) . '/vendor';

    require $vendorDir . '/autoload.php';
    require $vendorDir . '/yiisoft/yii2/Yii.php';
    require $vendorDir . '/craftcms/cms/src/Craft.php';
    require __DIR__ . '/../../src/base/OAuthProviderTrait.php';

    class FixtureRequest
    {
        public array $params = [];

        public function getParam(string $name): mixed
        {
            return $this->params[$name] ?? null;
        }
    }

    class FixtureApp
    {
        public FixtureRequest $request;

        public function __construct()
        {
            $this->request = new FixtureRequest();
        }

        public function getRequest(): FixtureRequest
        {
            return $this->request;
        }
    }

    class FixtureOwner
    {
        use verbb\auth\base\OAuthProviderTrait;

        public function __construct(League\OAuth2\Client\Provider\AbstractProvider $provider)
        {
            $this->_oauthProvider = $provider;
        }

        public static function getOAuthProviderClass(): string
        {
            return League\OAuth2\Client\Provider\GenericProvider::class;
        }

        public function getGrant(): string
        {
            return 'authorization_code';
        }
    }

    function check(string $label, bool $condition): void
    {
        if (!$condition) {
            throw new RuntimeException("Failed: {$label}");
        }

        echo "{$label}: PASS\n";
    }

    $app = new FixtureApp();
    Craft::$app = $app;

    $mock = new GuzzleHttp\Handler\MockHandler([
        new GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"access_token":"fixture-token","token_type":"bearer"}'),
    ]);
    $provider = new League\OAuth2\Client\Provider\GenericProvider([
        'clientId' => 'fixture-id',
        'clientSecret' => 'fixture-secret',
        'redirectUri' => 'https://site.test/callback',
        'urlAuthorize' => 'https://provider.test/authorize',
        'urlAccessToken' => 'https://provider.test/token',
        'urlResourceOwnerDetails' => 'https://provider.test/profile',
    ], [
        'httpClient' => new GuzzleHttp\Client(['handler' => $mock]),
    ]);
    $owner = new FixtureOwner($provider);

    foreach ([
        'missing callback state' => ['code' => 'code-a'],
        'empty callback state' => ['code' => 'code-b', 'state' => ''],
        'mismatched callback state' => ['code' => 'code-c', 'state' => 'other-state'],
    ] as $label => $params) {
        $app->request->params = $params;
        $owner->setOAuthTransactionData(['state' => 'expected-state']);
        $rejected = false;

        try {
            $owner->getAccessToken();
        } catch (Throwable) {
            $rejected = true;
        }

        check("{$label} is rejected before token exchange", $rejected);
    }

    $app->request->params = ['code' => 'valid-code', 'state' => 'expected-state'];
    $owner->setOAuthTransactionData(['state' => 'expected-state']);
    check('Matching non-empty state completes token exchange', $owner->getAccessToken()?->getToken() === 'fixture-token');
}
