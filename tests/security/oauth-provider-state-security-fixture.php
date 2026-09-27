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
    require __DIR__ . '/../../src/helpers/Redirect.php';

    class FixtureRequest
    {
        public array $params = [];
        public ?string $referrer = null;
        public string $hostInfo = 'https://site.test';

        public function getParam(string $name): mixed
        {
            return $this->params[$name] ?? null;
        }

        public function getBaseUrl(): string
        {
            return '';
        }

        public function getHostInfo(): string
        {
            return $this->hostInfo;
        }

        public function getReferrer(): ?string
        {
            return $this->referrer;
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

        public function getSecurity(): FixtureSecurity
        {
            return new FixtureSecurity();
        }
    }

    class FixtureSecurity extends yii\base\Security
    {
        public function hashData($data, $key = null, $rawHash = false): string
        {
            return parent::hashData($data, 'fixture-security-key', $rawHash);
        }

        public function validateData($data, $key = null, $rawHash = false): string|false
        {
            if (!is_string($data)) {
                return false;
            }

            return parent::validateData($data, 'fixture-security-key', $rawHash);
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

    $app->request->params = [];
    $app->request->referrer = 'https://site.test/account?marker={{7*7}}';
    $owner->getAuthorizationUrl();
    $returnData = $owner->getOAuthTransactionData();
    check('Same-origin referrer is retained as literal data', $returnData['redirect'] === $app->request->referrer && $returnData['origin'] === $app->request->referrer);

    $app->request->referrer = 'https://SITE.test:443/account';
    $owner->getAuthorizationUrl();
    $returnData = $owner->getOAuthTransactionData();
    check('Equivalent host casing and default port remain same-origin', $returnData['redirect'] === $app->request->referrer);

    foreach ([
        'cross-origin referrer' => 'https://evil.test/after-login',
        'protocol-relative referrer' => '//evil.test/after-login',
        'host-suffix referrer' => 'https://site.test.evil.test/after-login',
        'userinfo referrer' => 'https://site.test@evil.test/after-login',
        'scheme mismatch' => 'http://site.test/after-login',
        'port mismatch' => 'https://site.test:444/after-login',
        'backslash referrer' => 'https://site.test\\@evil.test/after-login',
        'control-character referrer' => "https://site.test/after-login\r\nX-Test: injected",
        'script referrer' => 'javascript:alert(1)',
    ] as $label => $referrer) {
        $app->request->referrer = $referrer;
        $owner->getAuthorizationUrl();
        $returnData = $owner->getOAuthTransactionData();
        check("{$label} falls back to the initiating site", $returnData['redirect'] === 'https://site.test/' && $returnData['origin'] === 'https://site.test/');
    }

    $signedExternalRedirect = 'https://frontend.test/account';
    $app->request->params = ['redirect' => $app->getSecurity()->hashData($signedExternalRedirect)];
    $app->request->referrer = 'https://evil.test/after-login';
    $owner->getAuthorizationUrl();
    $returnData = $owner->getOAuthTransactionData();
    check('A signed external redirect remains an explicit application destination', $returnData['redirect'] === $signedExternalRedirect && $returnData['origin'] === 'https://site.test/');

    foreach ([
        'unsigned external redirect' => 'https://evil.test/tampered',
        'signed non-HTTP redirect' => 'javascript:alert(1)',
    ] as $label => $unsafeRedirect) {
        $app->request->params = ['redirect' => $unsafeRedirect === 'https://evil.test/tampered' ? $unsafeRedirect : $app->getSecurity()->hashData($unsafeRedirect)];
        $owner->getAuthorizationUrl();
        $returnData = $owner->getOAuthTransactionData();
        check("{$label} falls back to the initiating site", $returnData['redirect'] === 'https://site.test/');
    }
}
