<?php
require (getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__) . '/vendor') . '/autoload.php';
require __DIR__ . '/../src/helpers/ProviderError.php';
require __DIR__ . '/../src/clients/salesforce/provider/Salesforce.php';

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\salesforce\provider\Salesforce;

class FixtureSalesforceProvider extends Salesforce
{
    public function checkFixtureResponse(ResponseInterface $response, mixed $data): void
    {
        $this->checkResponse($response, $data);
    }
}

function check(string $label, bool $condition): void
{
    if (!$condition) {
        throw new RuntimeException("{$label}: FAIL");
    }

    fwrite(STDOUT, "{$label}: PASS\n");
}

$provider = new FixtureSalesforceProvider();
$response = new Response(400);
$cases = [
    'OAuth description takes precedence' => [
        ['error' => 'invalid_grant', 'error_description' => 'authentication failure'],
        'invalid_grant: authentication failure',
    ],
    'OAuth code without a description' => [['error' => 'invalid_client_id'], 'invalid_client_id'],
    'Empty description falls back to the code' => [['error_description' => '', 'error' => 'invalid_grant'], 'invalid_grant'],
    'Whitespace description falls back to the code' => [['error_description' => " \t\n", 'error' => 'invalid_grant'], 'invalid_grant'],
    'Non-string description falls back to the code' => [['error_description' => [], 'error' => 'invalid_grant'], 'invalid_grant'],
    'REST errors retain the first message' => [
        [['message' => 'Session expired or invalid', 'errorCode' => 'INVALID_SESSION_ID'], ['message' => 'Second message']],
        'Session expired or invalid',
    ],
    'Empty REST message falls back to HTTP reason' => [[['message' => '']], 'Bad Request'],
    'Non-string errors fall back to HTTP reason' => [['error_description' => [], 'error' => false, ['message' => new stdClass()]], 'Bad Request'],
    'Malformed REST error falls back to HTTP reason' => [[new stdClass()], 'Bad Request'],
    'Non-JSON response falls back to HTTP reason' => ['<html>Bad Request</html>', 'Bad Request'],
    'Empty response falls back to HTTP reason' => [null, 'Bad Request'],
    'Unexpected object falls back to HTTP reason' => [new stdClass(), 'Bad Request'],
];

foreach ($cases as $label => [$data, $expectedMessage]) {
    try {
        $provider->checkFixtureResponse($response, $data);
        check($label . ' throws', false);
    } catch (IdentityProviderException $exception) {
        check($label, $exception->getMessage() === $expectedMessage);
        check($label . ' preserves status and response', $exception->getCode() === 400 && $exception->getResponseBody() === $response);
    }
}

$provider->checkFixtureResponse(new Response(200), ['error_description' => 'Ignored success message']);
check('Successful responses do not throw', true);

// Exercise the real token-response decoding path without contacting Salesforce.
$provider->setHttpClient(new Client(['handler' => HandlerStack::create(new MockHandler([
    new Response(400, ['Content-Type' => 'application/json'], json_encode([
        'error' => 'invalid_grant',
        'error_description' => 'authentication failure',
    ], JSON_THROW_ON_ERROR)),
]))]));

try {
    $provider->getAccessToken('password', ['username' => 'fixture@example.test', 'password' => 'fixture']);
    check('Token exchange throws', false);
} catch (IdentityProviderException $exception) {
    check('Token exchange exposes the OAuth description', $exception->getMessage() === 'invalid_grant: authentication failure');
}
