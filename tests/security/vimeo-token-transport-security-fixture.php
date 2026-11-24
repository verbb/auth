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
    use GuzzleHttp\Handler\MockHandler;
    use GuzzleHttp\HandlerStack;
    use GuzzleHttp\Middleware;
    use GuzzleHttp\Psr7\Response;
    use League\OAuth2\Client\Token\AccessToken;
    use verbb\auth\Auth;
    use verbb\auth\models\Token;
    use verbb\auth\providers\Vimeo;

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

    $secret = 'fixture-vimeo-token-1234567890';
    $history = [];
    $stack = HandlerStack::create(new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], '{"data":[],"paging":{"next":null}}'),
        new Response(404, ['Content-Type' => 'application/json'], '{"error":"This resource is restricted in your region."}'),
        new Response(200, ['Content-Type' => 'application/json'], '{"uri":"/users/1","name":"Fixture"}'),
    ]));
    $stack->push(Middleware::history($history));

    $provider = new Vimeo([], ['httpClient' => new Client(['handler' => $stack])]);
    $token = new Token();
    $token->setToken(new AccessToken(['access_token' => $secret]));

    $query = ['full_response' => 1, 'page' => 1, 'per_page' => 100, 'fields' => 'name,uri'];

    $data = $provider->getApiRequest('GET', 'me/albums', $token, ['query' => $query]);
    check('Successful request returns parsed data', $data === ['data' => [], 'paging' => ['next' => null]]);

    $request = $history[0]['request'];
    check('API request uses the Vimeo API host', (string)$request->getUri()->withQuery('') === 'https://api.vimeo.com/me/albums');
    check('API request keeps caller query params', $request->getUri()->getQuery() === 'full_response=1&page=1&per_page=100&fields=name%2Curi');
    check('API request sends the token as a Bearer header', $request->getHeaderLine('Authorization') === 'Bearer ' . $secret);
    check('API request URL has no access token', !str_contains((string)$request->getUri(), $secret));

    try {
        $provider->getApiRequest('GET', 'me/channels', $token, ['query' => $query]);
        throw new RuntimeException('Expected the 404 to be thrown.');
    } catch (ClientException $e) {
        check('Failed request keeps the Guzzle 404 exception', $e->getCode() === 404);
        check('Failed request message has no access token', !str_contains($e->getMessage(), $secret));
        check('Failed request message still names the endpoint', str_contains($e->getMessage(), 'https://api.vimeo.com/me/channels?'));
    }

    $logged = json_encode(Auth::$errors);
    check('Failed request was logged', count(Auth::$errors) === 1);
    check('Logged error has no access token', !str_contains($logged, $secret));

    $owner = $provider->getResourceOwner(new AccessToken(['access_token' => $secret]));
    $ownerRequest = $history[2]['request'];
    check('Account details are fetched', $owner->toArray()['name'] === 'Fixture');
    check('Account details request sends the token as a Bearer header', $ownerRequest->getHeaderLine('Authorization') === 'Bearer ' . $secret);
    check('Account details URL has no access token', (string)$ownerRequest->getUri() === 'https://api.vimeo.com/me');
}
