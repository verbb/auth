<?php
require (getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__) . '/vendor') . '/autoload.php';

spl_autoload_register(static function(string $class): void {
    $prefix = 'verbb\\auth\\';

    if (str_starts_with($class, $prefix)) {
        $path = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

        if (is_file($path)) {
            require $path;
        }
    }
}, true, true);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use verbb\auth\clients\linkedin\provider\LinkedIn;
use verbb\auth\clients\linkedin\provider\exception\LinkedInAccessDeniedException;

function check(string $label, bool $condition): void
{
    if (!$condition) {
        throw new RuntimeException("Failed: {$label}");
    }

    echo "{$label}: PASS\n";
}

$cases = [
    ['created without body', new Response(201, ['x-restli-id' => 'urn:li:share:123']), []],
    ['created with empty JSON body', new Response(201, ['Content-Type' => 'application/json']), []],
    ['upload with no content', new Response(204), []],
    ['JSON response', new Response(200, ['Content-Type' => 'application/json'], '{"sub":"person-123"}'), ['sub' => 'person-123']],
    ['binary response', new Response(200, ['Content-Type' => 'image/png'], "\x89PNG\r\n"), "\x89PNG\r\n"],
];

foreach ($cases as [$label, $response, $expected]) {
    $provider = new LinkedIn([], ['httpClient' => new Client([
        'handler' => HandlerStack::create(new MockHandler([$response])),
        'http_errors' => false,
    ])]);
    $request = $provider->getAuthenticatedRequest('POST', 'https://api.linkedin.com/rest/posts', 'fixture-token');
    check($label, $provider->getParsedResponse($request) === $expected);
}

$errors = [
    [new Response(403, ['Content-Type' => 'application/json'], '{"status":403,"message":"Access denied"}'), LinkedInAccessDeniedException::class, 'Access denied'],
    [new Response(401, ['Content-Type' => 'text/plain'], 'Unauthorized'), IdentityProviderException::class, 'Unauthorized'],
    [new Response(404, ['Content-Type' => 'application/json'], '{"status":404,"message":"No virtual resource found"}'), IdentityProviderException::class, 'No virtual resource found'],
    [new Response(400, ['Content-Type' => 'application/json']), IdentityProviderException::class, 'Bad Request'],
];

foreach ($errors as [$response, $exceptionClass, $message]) {
    $provider = new LinkedIn([], ['httpClient' => new Client([
        'handler' => HandlerStack::create(new MockHandler([$response])),
        'http_errors' => false,
    ])]);
    $request = $provider->getAuthenticatedRequest('POST', 'https://api.linkedin.com/rest/posts', 'fixture-token');

    try {
        $provider->getParsedResponse($request);
        throw new RuntimeException('Expected the API error to be preserved.');
    } catch (IdentityProviderException $e) {
        check("HTTP {$response->getStatusCode()} remains an API error", $e instanceof $exceptionClass && $e->getCode() === $response->getStatusCode() && $e->getMessage() === $message);
    }
}
