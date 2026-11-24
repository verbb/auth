<?php
namespace verbb\auth {
    class Auth
    {
        public static array $errors = [];

        // Token reloads find nothing stored, so the in-memory fixture token is used as-is.
        public static function getInstance(): object
        {
            return new class {
                public function getTokens(): object
                {
                    return new class {
                        public function getTokenById(int $id): mixed
                        {
                            return null;
                        }

                        public function getTokenByOwnerReference(string $ownerHandle, mixed $reference): mixed
                        {
                            return null;
                        }
                    };
                }
            };
        }

        public static function error(string $message, array $params = []): void
        {
            self::$errors[] = [$message, $params];
        }
    }
}

namespace {
    $vendorDir = getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__, 2) . '/vendor';

    require $vendorDir . '/autoload.php';
    require $vendorDir . '/yiisoft/yii2/Yii.php';
    require $vendorDir . '/craftcms/cms/src/Craft.php';

    // Load Auth from this checkout before any copy installed in the test vendor tree.
    spl_autoload_register(static function(string $class): void {
        $prefix = 'verbb\\auth\\';

        if (str_starts_with($class, $prefix)) {
            $path = dirname(__DIR__, 2) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

            if (is_file($path)) {
                require $path;
            }
        }
    }, true, true);

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\ClientException;
    use GuzzleHttp\Exception\ConnectException;
    use GuzzleHttp\Handler\MockHandler;
    use GuzzleHttp\HandlerStack;
    use GuzzleHttp\Middleware;
    use GuzzleHttp\Psr7\Response;
    use League\OAuth2\Client\Token\AccessToken;
    use verbb\auth\Auth;
    use verbb\auth\models\Token;
    use verbb\auth\providers\Disqus;

    // `UrlHelper::buildQuery()` only reads the general config's `pathParam`.
    Craft::$app = new class {
        public function getConfig(): object
        {
            return new class {
                public function getGeneral(): object
                {
                    return (object)['pathParam' => 'p'];
                }
            };
        }
    };

    function check(string $label, bool $condition): void
    {
        if (!$condition) {
            throw new RuntimeException("Failed: {$label}");
        }

        echo "{$label}: PASS\n";
    }

    function makeToken(string $accessToken, ?string $refreshToken = null): Token
    {
        $token = new Token();
        $token->setToken(new AccessToken(array_filter([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ])));

        return $token;
    }

    $secret = 'fixture-provider-token-1234567890';

    // Providers whose APIs accept the token as a Bearer header no longer put it in the URL.
    $headerProviders = [
        'Aweber' => [[], 'https://api.aweber.com/1.0/accounts', null],
        'Dribbble' => [[], 'https://api.dribbble.com/v2/accounts', 'https://api.dribbble.com/v2/user'],
        'Marketo' => [['apiDomain' => 'https://123-abc-456.mktorest.com'], 'https://123-abc-456.mktorest.com/accounts', null],
        'Meetup' => [[], 'https://api.meetup.com/2/accounts', 'https://api.meetup.com/2/member/self'],
        'Microsoft' => [[], 'https://graph.microsoft.com/v1.0/accounts', null],
        'MicrosoftEntra' => [[], 'https://graph.microsoft.com/v1.0/accounts', null],
        'Pinterest' => [[], 'https://api.pinterest.com/v1/accounts', 'https://api.pinterest.com/v1/me/?fields=first_name%2Cid%2Clast_name%2Curl%2Cimage%2Cusername%2Ccreated_at%2Ccounts'],
        'Unsplash' => [[], 'https://api.unsplash.com/accounts', 'https://api.unsplash.com/me'],
    ];

    foreach ($headerProviders as $name => [$options, $expectedUrl, $ownerUrl]) {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], '{"data":[]}'),
        ]));
        $stack->push(Middleware::history($history));

        $class = "verbb\\auth\\providers\\{$name}";
        $provider = new $class($options, ['httpClient' => new Client(['handler' => $stack])]);

        $data = $provider->getApiRequest('GET', 'accounts', makeToken($secret), ['query' => ['page' => 2]]);
        $request = $history[0]['request'];

        check("{$name} request returns parsed data", $data === ['data' => []]);
        check("{$name} request keeps the endpoint and caller query", (string)$request->getUri() === $expectedUrl . '?page=2');
        check("{$name} request sends the token as a Bearer header", $request->getHeaderLine('Authorization') === 'Bearer ' . $secret);

        if ($ownerUrl) {
            check("{$name} account details URL has no access token", $provider->getResourceOwnerDetailsUrl(new AccessToken(['access_token' => $secret])) === $ownerUrl);
        }
    }

    // Providers that still need the token in the URL have it removed from errors and logs.
    Auth::$errors = [];
    $history = [];
    $stack = HandlerStack::create(new MockHandler([
        new Response(404, ['Content-Type' => 'application/json'], '{"code":2,"response":"Invalid thread"}'),
        static fn($request) => throw new ConnectException('cURL error 28: Operation timed out (see https://curl.se/libcurl/c/libcurl-errors.html) for ' . $request->getUri(), $request),
    ]));
    $stack->push(Middleware::history($history));

    $provider = new Disqus([], ['httpClient' => new Client(['handler' => $stack])]);
    $token = makeToken($secret);

    try {
        $provider->getApiRequest('GET', 'threads/details.json', $token, ['query' => ['thread' => 7]], false);
        throw new RuntimeException('Expected the 404 to be thrown.');
    } catch (ClientException $e) {
        check('Failed request keeps the Guzzle 404 exception', $e->getCode() === 404 && $e->getResponse()->getStatusCode() === 404);
        check('Failed request message has no access token', !str_contains($e->getMessage(), $secret));
        check('Failed request message still names the endpoint', str_contains($e->getMessage(), 'https://disqus.com/api/3.0/threads/details.json?access_token=[redacted]&thread=7'));
    }

    check('Failed request still sent the token to the provider', str_contains((string)$history[0]['request']->getUri(), 'access_token=' . $secret));

    try {
        $provider->getApiRequest('GET', 'threads/details.json', $token, [], false);
        throw new RuntimeException('Expected the connection error to be thrown.');
    } catch (ConnectException $e) {
        check('Connection error message has no access token', !str_contains($e->getMessage(), $secret));
    }

    $logged = json_encode(Auth::$errors);
    check('Both failures were logged', count(Auth::$errors) === 2);
    check('Logged errors have no access token', !str_contains($logged, $secret));

    // Caller-supplied secrets in the query are redacted by name, even when they aren't the stored token.
    Auth::$errors = [];
    $stack = HandlerStack::create(new MockHandler([
        new Response(400, ['Content-Type' => 'application/json'], '{"error":"bad"}'),
    ]));
    $provider = new Disqus([], ['httpClient' => new Client(['handler' => $stack])]);

    try {
        $provider->getApiRequest('GET', 'users/details.json', $token, ['query' => ['client_secret' => 'fixture-client-secret', 'user' => 'me']], false);
        throw new RuntimeException('Expected the 400 to be thrown.');
    } catch (ClientException $e) {
        check('Caller query secret is redacted', !str_contains($e->getMessage(), 'fixture-client-secret') && str_contains($e->getMessage(), 'client_secret=[redacted]&user=me'));
    }

    // Token refresh failures redact the access and refresh tokens before logging.
    Auth::$errors = [];
    $refreshSecret = 'fixture-refresh-token-0987654321';
    $refreshProvider = new class([]) extends Disqus {
        public function getRefreshToken(League\OAuth2\Client\Token\AccessToken $accessToken): League\OAuth2\Client\Token\AccessTokenInterface|League\OAuth2\Client\Token\AccessToken|null
        {
            throw new RuntimeException('Refresh failed for https://disqus.com/api/oauth/2.0/access_token/?refresh_token=' . $accessToken->getRefreshToken() . ' using ' . $accessToken->getToken());
        }
    };

    $refreshProvider->refreshToken(makeToken($secret, $refreshSecret), true);
    $logged = json_encode(Auth::$errors);
    check('Refresh failure was logged', count(Auth::$errors) === 1);
    check('Refresh failure log has no tokens', !str_contains($logged, $secret) && !str_contains($logged, $refreshSecret));
}
