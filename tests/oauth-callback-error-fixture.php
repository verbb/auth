<?php
namespace verbb\auth {
    class Auth
    {
        public static array $errors = [];

        public static function error(string $message, array $params = []): void
        {
            self::$errors[] = [$message, $params];
        }
    }
}

namespace {
    $vendorDir = getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__) . '/vendor';

    require $vendorDir . '/autoload.php';
    require $vendorDir . '/yiisoft/yii2/Yii.php';
    require $vendorDir . '/craftcms/cms/src/Craft.php';
    require __DIR__ . '/../src/base/OAuthProviderTrait.php';
    require __DIR__ . '/../src/helpers/Redirect.php';

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

    function callbackError(FixtureOwner $owner): ?string
    {
        try {
            $owner->getAccessToken();
        } catch (Throwable $e) {
            return $e->getMessage();
        }

        return null;
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

    // LinkedIn-style callback: standard `error` and `error_description`, with state but no code
    $app->request->params = [
        'error' => 'unauthorized_scope_error',
        'error_description' => 'Scope "r_organization_social" is not authorized for your application',
        'state' => 'expected-state',
    ];
    $owner->setOAuthTransactionData(['state' => 'expected-state']);
    check('Standard error and description are reported', callbackError($owner) === 'The provider returned an error: unauthorized_scope_error: Scope "r_organization_social" is not authorized for your application');
    check('Standard error is logged', (verbb\auth\Auth::$errors[0][1]['error'] ?? null) === 'unauthorized_scope_error: Scope "r_organization_social" is not authorized for your application');

    $app->request->params = ['error' => 'access_denied', 'state' => 'expected-state'];
    check('Standard error without a description is reported', callbackError($owner) === 'The provider returned an error: access_denied');

    // Facebook-style callback
    $app->request->params = ['error_code' => '200', 'error_message' => 'Permissions error'];
    check('Error code and message are reported', callbackError($owner) === 'The provider returned an error: 200: Permissions error');

    $app->request->params = ['error' => 'access_denied', 'error_code' => '200', 'error_description' => 'Permissions error', 'error_reason' => 'user_denied'];
    check('Readable error is preferred over a numeric code', callbackError($owner) === 'The provider returned an error: access_denied: Permissions error');

    $app->request->params = ['error_code' => ['code' => 190]];
    check('Array error values are encoded', callbackError($owner) === 'The provider returned an error: {"code":190}');

    // Callbacks without an error keep their existing behaviour
    $app->request->params = ['state' => 'expected-state'];
    check('Missing code without an error is still rejected', callbackError($owner) === 'Invalid callback authorization code.');

    $app->request->params = ['code' => 'valid-code', 'state' => 'expected-state'];
    check('Valid callback completes token exchange', $owner->getAccessToken()?->getToken() === 'fixture-token');
}
