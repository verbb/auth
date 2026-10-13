<?php
$vendorDir = getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__, 2) . '/vendor';

require $vendorDir . '/autoload.php';
require __DIR__ . '/../../src/base/ProviderTrait.php';
require __DIR__ . '/../../src/clients/basecamp/provider/Basecamp.php';
require __DIR__ . '/../../src/providers/Basecamp.php';

use verbb\auth\providers\Basecamp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Token\AccessToken;

set_error_handler(static function(int $severity, string $message, string $file, int $line): never {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function check(string $description, bool $condition): void
{
    if (!$condition) {
        throw new RuntimeException("Failed: $description");
    }

    echo "PASS: $description\n";
}

function exchange(string $grant, array $options, bool $wrapper = false, bool $fail = false, array $config = []): array
{
    $history = [];
    $handler = new MockHandler([$fail ? static function($request) {
        // Model a transport error carrying the complete URI, without changing diagnostics.
        throw new ConnectException('Fixture connection failure for ' . $request->getUri(), $request);
    } : new Response(200, [], json_encode([
        'access_token' => 'fixture-new-access',
        'refresh_token' => 'fixture-new-refresh',
        'expires_in' => 3600,
    ], JSON_THROW_ON_ERROR))]);
    $stack = HandlerStack::create($handler);
    $stack->push(Middleware::history($history));
    $provider = new Basecamp($config + [
        'clientId' => 'fixture-client',
        'clientSecret' => 'fixture-secret',
        'redirectUri' => 'https://site.test/callback',
    ], ['httpClient' => new Client(['handler' => $stack])]);
    $result = null;
    $error = null;

    try {
        $result = $wrapper
            ? $provider->getRefreshToken(new AccessToken(['access_token' => 'fixture-old-access'] + $options))
            : $provider->getAccessToken($grant, $options);
    } catch (ConnectException $e) {
        $error = $e;
    }

    return [$history[0]['request'] ?? $error?->getRequest(), $result, $error];
}

foreach (['fixture-refresh', 'fixture+/%&= token?雪'] as $refresh) {
    foreach ([false, true] as $wrapper) {
        [$request, $token] = exchange('refresh_token', ['refresh_token' => $refresh], $wrapper);
        parse_str((string)$request->getBody(), $body);
        check('Refresh URI contains only the existing flow selector', (string)$request->getUri() === 'https://launchpad.37signals.com/authorization/token?type=web_server');
        check('Refresh remains a form-encoded POST', $request->getMethod() === 'POST' && str_starts_with($request->getHeaderLine('Content-Type'), 'application/x-www-form-urlencoded'));
        check('Complete refresh body and exact token bytes are preserved', $body === [
            'client_id' => 'fixture-client',
            'client_secret' => 'fixture-secret',
            'redirect_uri' => 'https://site.test/callback',
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh,
        ]);
        check('Returned access and rotated refresh tokens remain available', $token->getToken() === 'fixture-new-access' && $token->getRefreshToken() === 'fixture-new-refresh');
    }
}

[$request] = exchange('refresh_token', ['refresh_token' => 'fixture-refresh', 'type' => 'refresh'], false, false, ['host' => 'https://basecamp-proxy.test/']);
parse_str((string)$request->getBody(), $body);
check('Configured host and explicit legacy flow selector remain supported', (string)$request->getUri() === 'https://basecamp-proxy.test/authorization/token?type=refresh');
check('Explicit flow selector and refresh credential remain in the body', $body['type'] === 'refresh' && $body['refresh_token'] === 'fixture-refresh');

[$request, $token] = exchange('authorization_code', ['code' => 'fixture-code']);
parse_str((string)$request->getBody(), $body);
check('Initial token exchange URI is unchanged', (string)$request->getUri() === 'https://launchpad.37signals.com/authorization/token?type=web_server');
check('Initial token exchange body is unchanged', $body === [
    'client_id' => 'fixture-client',
    'client_secret' => 'fixture-secret',
    'redirect_uri' => 'https://site.test/callback',
    'grant_type' => 'authorization_code',
    'code' => 'fixture-code',
]);
check('Initial token exchange still returns an access token', $token->getToken() === 'fixture-new-access');

[$request, $token, $error] = exchange('refresh_token', ['refresh_token' => 'fixture-refresh'], true, true);
check('Transport errors still propagate with useful diagnostics', $token === null && $error instanceof ConnectException && str_contains($error->getMessage(), 'Fixture connection failure for https://launchpad.37signals.com/authorization/token?type=web_server'));
check('URL-derived diagnostics no longer contain the refresh credential', !str_contains($error->getMessage(), 'fixture-refresh'));
parse_str((string)$error->getRequest()->getBody(), $body);
check('Failure request retains the refresh credential in its body', $body['refresh_token'] === 'fixture-refresh');
