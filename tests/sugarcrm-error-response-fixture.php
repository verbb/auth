<?php
require (getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__) . '/vendor') . '/autoload.php';
require __DIR__ . '/../src/clients/sugarcrm/provider/Sugarcrm.php';

use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\sugarcrm\provider\Sugarcrm;

class FixtureSugarcrmProvider extends Sugarcrm
{
    public function checkFixtureResponse(ResponseInterface $response, mixed $data): void
    {
        $this->checkResponse($response, $data);
    }
}

function check(string $label, bool $condition): void
{
    if (!$condition) {
        fwrite(STDERR, "{$label}: FAIL\n");
        exit(1);
    }

    fwrite(STDOUT, "{$label}: PASS\n");
}

$provider = (new ReflectionClass(FixtureSugarcrmProvider::class))->newInstanceWithoutConstructor();
$response = new Response(400, [], null, '1.1', 'Bad Request');
$cases = [
    'SugarCRM error_message is preferred' => [
        ['error_message' => 'Readable message', 'description' => 'Legacy description', 'error' => 'invalid_grant'],
        'Readable message',
    ],
    'SugarCRM description remains supported' => [
        ['description' => 'Legacy description', 'error' => 'invalid_grant'],
        'Legacy description',
    ],
    'SugarCRM error code is used when no message is available' => [
        ['error' => 'invalid_grant'],
        'invalid_grant',
    ],
    'Empty SugarCRM messages fall through to the next field' => [
        ['error_message' => '', 'description' => 'Legacy description'],
        'Legacy description',
    ],
    'Non-string SugarCRM errors fall back to the HTTP reason' => [
        ['error' => ['code' => 'invalid_grant']],
        'Bad Request',
    ],
    'Non-JSON SugarCRM errors fall back to the HTTP reason' => [
        'invalid response',
        'Bad Request',
    ],
];

foreach ($cases as $label => [$data, $expectedMessage]) {
    try {
        $provider->checkFixtureResponse($response, $data);
        check($label, false);
    } catch (IdentityProviderException $exception) {
        check($label, $exception->getMessage() === $expectedMessage);
    }
}

$provider->checkFixtureResponse(new Response(200), ['error_message' => 'Ignored success message']);
check('Successful SugarCRM responses do not throw', true);
