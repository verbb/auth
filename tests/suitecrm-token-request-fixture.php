<?php
require (getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__) . '/vendor') . '/autoload.php';
require __DIR__ . '/../src/clients/suitecrm/provider/SuiteCrm.php';

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use verbb\auth\clients\suitecrm\provider\SuiteCrm;

function check(string $label, bool $condition): void
{
    if (!$condition) {
        throw new RuntimeException("Failed: {$label}");
    }

    echo "{$label}: PASS\n";
}

$clientId = 'fixture-client';
$clientSecret = 'fixture+secret:/@%';
$tokenUrl = 'https://suitecrm.example.test/crm/Api/access_token';
$grants = [
    'authorization_code' => ['code' => 'fixture-code'],
    'password' => ['username' => 'fixture-user', 'password' => 'fixture-password'],
    'refresh_token' => ['refresh_token' => 'fixture-refresh'],
];

foreach ($grants as $grant => $grantOptions) {
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'access_token' => 'fresh-token',
            'refresh_token' => 'fresh-refresh',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], JSON_THROW_ON_ERROR)),
    ]);
    $handler = function(RequestInterface $request, array $options) use ($mock, $grant, $grantOptions, $clientId, $clientSecret, $tokenUrl) {
        // Formie's guarded delivery transport rejects token URLs containing user info.
        check("{$grant}: token URI contains no user info", $request->getUri()->getUserInfo() === '');
        check("{$grant}: installation token endpoint is preserved", (string)$request->getUri() === $tokenUrl);
        check("{$grant}: credentials use HTTP Basic authentication", $request->getHeaderLine('Authorization') === 'Basic ' . base64_encode($clientId . ':' . $clientSecret));
        check("{$grant}: token request uses POST", $request->getMethod() === 'POST');

        parse_str((string)$request->getBody(), $body);
        check("{$grant}: body credentials are preserved", ($body['client_id'] ?? null) === $clientId && ($body['client_secret'] ?? null) === $clientSecret);
        check("{$grant}: grant type is preserved", ($body['grant_type'] ?? null) === $grant);

        foreach ($grantOptions as $name => $value) {
            check("{$grant}: {$name} is preserved", ($body[$name] ?? null) === $value);
        }

        return $mock($request, $options);
    };
    $provider = new SuiteCrm([
        'clientId' => $clientId,
        'clientSecret' => $clientSecret,
        'redirectUri' => 'https://site.example.test/callback',
        'apiDomain' => 'https://suitecrm.example.test/crm/',
    ], [
        'httpClient' => new Client(['handler' => $handler]),
    ]);

    $token = $provider->getAccessToken($grant, $grantOptions);
    check("{$grant}: token response is accepted", $token->getToken() === 'fresh-token' && $token->getRefreshToken() === 'fresh-refresh');
}
