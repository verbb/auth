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
    $vendorDir = getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__) . '/vendor';

    require $vendorDir . '/autoload.php';
    require $vendorDir . '/yiisoft/yii2/Yii.php';
    require $vendorDir . '/craftcms/cms/src/Craft.php';
    require __DIR__ . '/../src/base/OAuthProviderInterface.php';
    require __DIR__ . '/../src/models/Token.php';
    require __DIR__ . '/../src/services/Tokens.php';
    require __DIR__ . '/../src/base/OAuthProviderTrait.php';

    use verbb\auth\Auth;
    use verbb\auth\base\OAuthProviderInterface;
    use verbb\auth\base\OAuthProviderTrait;
    use verbb\auth\models\Token;
    use verbb\auth\services\Tokens;

    class FixtureConfig
    {
        public function getConfigFromFile(string $filename): array
        {
            return [];
        }

        public function getGeneral(): object
        {
            return (object)['httpProxy' => null];
        }
    }

    class FixtureApp
    {
        public function getVersion(): string
        {
            return '5.0.0';
        }

        public function getConfig(): FixtureConfig
        {
            return new FixtureConfig();
        }
    }

    class FixtureAuth
    {
        public function __construct(private Tokens $tokens)
        {
        }

        public function getTokens(): Tokens
        {
            return $this->tokens;
        }
    }

    class FixtureOAuthProvider extends League\OAuth2\Client\Provider\GenericProvider
    {
        public ?Token $requestToken = null;

        public function getApiRequest(string $method, string $uri, ?Token $token, array $options): array
        {
            $this->requestToken = $token;

            return $token?->values ?? [];
        }
    }

    class FixtureOwner implements OAuthProviderInterface
    {
        use OAuthProviderTrait;

        public function __construct(FixtureOAuthProvider $provider, private Token $storedToken)
        {
            $this->_oauthProvider = $provider;
        }

        public static function getOAuthProviderClass(): string
        {
            return FixtureOAuthProvider::class;
        }

        public function getGrant(): string
        {
            return 'client_credentials';
        }

        public function getToken(): ?Token
        {
            return $this->storedToken;
        }

        public function getBaseApiUrl(?Token $token): ?string
        {
            return null;
        }
    }

    function check(string $label, bool $condition): void
    {
        if (!$condition) {
            throw new RuntimeException("Failed: {$label}");
        }

        echo "{$label}: PASS\n";
    }

    Craft::$app = new FixtureApp();
    Auth::$instance = new FixtureAuth(new Tokens());

    $mock = new GuzzleHttp\Handler\MockHandler([
        new GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode([
            'access_token' => 'fresh-token',
            'instance_url' => 'https://example.my.salesforce.com',
            'token_type' => 'bearer',
        ], JSON_THROW_ON_ERROR)),
    ]);
    $provider = new FixtureOAuthProvider([
        'clientId' => 'fixture-id',
        'clientSecret' => 'fixture-secret',
        'redirectUri' => 'https://site.test/callback',
        'urlAuthorize' => 'https://provider.test/authorize',
        'urlAccessToken' => 'https://provider.test/token',
        'urlResourceOwnerDetails' => 'https://provider.test/profile',
    ], [
        'httpClient' => new GuzzleHttp\Client(['handler' => $mock]),
    ]);
    $storedToken = new Token([
        'ownerHandle' => 'formie',
        'tokenType' => Token::TOKEN_TYPE_OAUTH2,
        'accessToken' => 'stored-token',
    ]);
    $owner = new FixtureOwner($provider, $storedToken);

    $values = $owner->request('GET', 'sobjects/Lead');

    check('Client Credentials requests preserve provider response values', $values['instance_url'] === 'https://example.my.salesforce.com');
    check('Client Credentials requests use the fresh access token', $provider->requestToken?->accessToken === 'fresh-token');
    check('Per-request tokens stay detached from the stored token record', $provider->requestToken?->id === null);
}
