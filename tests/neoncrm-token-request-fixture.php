<?php
require (getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__) . '/vendor') . '/autoload.php';
require __DIR__ . '/../src/helpers/ProviderError.php';
require __DIR__ . '/../src/clients/neoncrm/provider/NeonCrm.php';

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\RequestInterface;
use verbb\auth\clients\neoncrm\provider\NeonCrm;

function check(string $label, bool $condition): void
{
    if (!$condition) {
        throw new RuntimeException("Failed: {$label}");
    }

    echo "{$label}: PASS\n";
}

/**
 * Build a provider over the real Guzzle handler stack, so its redirect middleware runs.
 * Each request's method, URI and body are recorded as they reach the transport.
 */
function provider(array $responses, array &$requests): NeonCrm
{
    $mock = new MockHandler($responses);
    $handler = function(RequestInterface $request, array $options) use ($mock, &$requests) {
        $requests[] = [
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
            'body' => (string)$request->getBody(),
        ];

        return $mock($request, $options);
    };

    return new NeonCrm([
        'clientId' => 'fixture-client',
        'clientSecret' => 'fixture-secret',
        'redirectUri' => 'https://site.example.test/callback',
        'organizationId' => 'fixture-org',
    ], [
        'httpClient' => new Client(['handler' => HandlerStack::create($handler)]),
    ]);
}

function tokenResponse(): Response
{
    return new Response(200, ['Content-Type' => 'application/json'], json_encode([
        'access_token' => 'fresh-token',
        'refresh_token' => 'fresh-refresh',
        'token_type' => 'Bearer',
        'expires_in' => 3600,
    ], JSON_THROW_ON_ERROR));
}

$tokenUrl = 'https://fixture-org.app.neoncrm.com/np/oauth/token';
$customUrl = 'https://secure.example.test/nx/auth/oauth/token';

// Organizations using Neon's default domain get the token straight away.
$requests = [];
$token = provider([tokenResponse()], $requests)->getAccessToken('authorization_code', ['code' => 'fixture-code']);
check('default domain: one POST to the token endpoint', count($requests) === 1 && $requests[0]['method'] === 'POST' && $requests[0]['uri'] === $tokenUrl);
check('default domain: token is accepted', $token->getToken() === 'fresh-token');

// A custom domain redirects the token endpoint, and the exchange has to survive it for every grant.
$grants = [
    'authorization_code' => ['code', 'fixture-code'],
    'refresh_token' => ['refresh_token', 'fixture-refresh'],
];

foreach ($grants as $grant => [$name, $value]) {
    foreach ([301, 302] as $status) {
        $requests = [];
        $token = provider([new Response($status, ['Location' => $customUrl]), tokenResponse()], $requests)->getAccessToken($grant, [$name => $value]);

        check("{$grant} {$status}: redirect is followed once", count($requests) === 2 && $requests[1]['uri'] === $customUrl);
        check("{$grant} {$status}: redirect stays a POST", $requests[1]['method'] === 'POST');
        check("{$grant} {$status}: redirect keeps the request body", $requests[1]['body'] !== '' && $requests[1]['body'] === $requests[0]['body']);

        parse_str($requests[1]['body'], $body);
        check("{$grant} {$status}: grant parameters are preserved", ($body['grant_type'] ?? null) === $grant && ($body[$name] ?? null) === $value);
        check("{$grant} {$status}: token is accepted", $token->getToken() === 'fresh-token' && $token->getRefreshToken() === 'fresh-refresh');
    }
}

// Credentials must never be replayed over plain HTTP.
$requests = [];
$failed = false;

try {
    provider([new Response(302, ['Location' => 'http://secure.example.test/nx/auth/oauth/token']), tokenResponse()], $requests)->getAccessToken('authorization_code', ['code' => 'fixture-code']);
} catch (Throwable) {
    $failed = true;
}

check('http redirect: exchange fails', $failed);
check('http redirect: nothing is sent to the insecure URL', count($requests) === 1 && $requests[0]['uri'] === $tokenUrl);

// Neon reports some failures as a 200 with its own response format.
$requests = [];
$error = null;

try {
    provider([new Response(200, ['Content-Type' => 'application/json'], json_encode([
        'success' => false,
        'data' => null,
        'messages' => [['code' => 'E-0005', 'messageType' => 'ERROR', 'message' => "Request method 'GET' not supported"]],
    ], JSON_THROW_ON_ERROR))], $requests)->getAccessToken('authorization_code', ['code' => 'fixture-code']);
} catch (IdentityProviderException $e) {
    $error = $e;
}

check('neon error: reported as a provider error', $error !== null);
check('neon error: message comes from the response', $error->getMessage() === "Request method 'GET' not supported");

// Existing OAuth errors keep working.
$requests = [];
$error = null;

try {
    provider([new Response(400, ['Content-Type' => 'application/json'], json_encode([
        'error' => 'invalid_grant',
        'error_description' => 'Code expired',
    ], JSON_THROW_ON_ERROR))], $requests)->getAccessToken('authorization_code', ['code' => 'fixture-code']);
} catch (IdentityProviderException $e) {
    $error = $e;
}

check('oauth error: message is preserved', $error !== null && $error->getMessage() === 'invalid_grant: Code expired');

// Only the token endpoint gets the stricter redirect handling.
$requests = [];
provider([new Response(302, ['Location' => 'https://api.example.test/next']), new Response(200)], $requests)->getResponse(new Request('POST', 'https://api.example.test/start', [], 'body'));
check('other requests: default redirect handling is unchanged', count($requests) === 2 && $requests[1]['method'] === 'GET' && $requests[1]['body'] === '');
