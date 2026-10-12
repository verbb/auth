<?php
$vendorDir = getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__, 2) . '/vendor';

require $vendorDir . '/autoload.php';
require __DIR__ . '/../../src/clients/telegram/provider/Telegram.php';
require __DIR__ . '/../../src/clients/telegram/provider/TelegramUser.php';

use verbb\auth\clients\telegram\provider\Telegram;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;

const BOT_SECRET = 'telegram-fixture-only-bot-secret';

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

function signPayload(array $payload): array
{
    unset($payload['hash']);
    ksort($payload, SORT_STRING);
    $lines = [];

    foreach ($payload as $key => $value) {
        $lines[] = "$key=$value";
    }

    $payload['hash'] = hash_hmac('sha256', implode("\n", $lines), hash('sha256', BOT_SECRET, true));

    return $payload;
}

function authenticate(Telegram $provider, array $payload): ?AccessToken
{
    return $provider->getAccessToken('authorization_code', [
        'code' => base64_encode(json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION)),
    ]);
}

function hasNoIdentity(Telegram $provider, AccessToken $token): bool
{
    try {
        $provider->getResourceOwner($token);
    } catch (IdentityProviderException) {
        return true;
    }

    return false;
}

$provider = new Telegram([
    'clientId' => '123',
    'clientSecret' => BOT_SECRET,
    'redirectUri' => 'https://site.test/callback',
]);
$base = ['auth_date' => time(), 'first_name' => 'Eve', 'id' => 555, 'last_name' => 'Smith'];
$signed = signPayload($base);
$token = authenticate($provider, $signed);
check('Genuine signed integer identity is preserved', $token !== null && $provider->getResourceOwner($token)->getId() === 555);

$folded = $signed;
$folded['id'] = "555\nlast_name=Smith";
unset($folded['last_name']);
check('Reported field-folding attack preserves the original signature', signPayload($folded)['hash'] === $signed['hash']);
check('Reported field-folding attack is rejected', authenticate($provider, $folded) === null);
check('Rejected payload cannot become a resource owner', hasNoIdentity($provider, $token));

$folded = $signed;
$folded['auth_date'] = $base['auth_date'] . "\nfirst_name=Eve";
unset($folded['first_name']);
check('Timestamp field-folding also preserves the original signature', signPayload($folded)['hash'] === $signed['hash']);
check('Timestamp field-folding is rejected', authenticate($provider, $folded) === null);

foreach ([['id' => '555', 'auth_date' => (string)$base['auth_date']], ['id' => '00555']] as $changes) {
    $payload = signPayload(array_replace($base, $changes));
    $validToken = authenticate($provider, $payload);
    check('Digit strings retain their signed representation', $validToken !== null && $provider->getResourceOwner($validToken)->toArray() === $payload);
}

$payload = signPayload(['auth_date' => time(), 'id' => 555, 'first_name' => "Zoë = 雪\nSecond line"]);
$validToken = authenticate($provider, $payload);
check('Optional fields may be absent and profile text remains unchanged', $validToken !== null && $provider->getResourceOwner($validToken)->toArray() === $payload);

$payload = signPayload($base + ['custom_field' => 'extra=value']);
$validToken = authenticate($provider, $payload);
check('Additional signed scalar fields remain supported', $validToken !== null && $provider->getResourceOwner($validToken)->toArray() === $payload);

$largeId = '9223372036854775808';
$payload = signPayload(array_replace($base, ['id' => $largeId]));
$json = str_replace('"id":"' . $largeId . '"', '"id":' . $largeId, json_encode($payload, JSON_THROW_ON_ERROR));
$validToken = $provider->getAccessToken('authorization_code', ['code' => base64_encode($json)]);
check('Large JSON integer identities retain every digit', $validToken !== null && $provider->getResourceOwner($validToken)->getId() === $largeId);

foreach (['id', 'auth_date'] as $field) {
    foreach ([null, true, false, [], 555.0, '5e2', '+555', '-555', ' 555', "555\n", ''] as $invalid) {
        $payload = array_replace($signed, [$field => $invalid]);
        check("Malformed $field value is rejected", authenticate($provider, $payload) === null);
    }

    $payload = $signed;
    unset($payload[$field]);
    check("Missing $field is rejected", authenticate($provider, $payload) === null);
}

foreach ([null, [], true, str_repeat('g', 64), str_repeat('a', 63), $signed['hash'] . "\n", str_repeat('0', 64)] as $hash) {
    check('Malformed or incorrect signature is rejected', authenticate($provider, array_replace($signed, ['hash' => $hash])) === null);
}

foreach ([[], true, null, 1.5, (object)['name' => 'Eve']] as $value) {
    check('Non-scalar or ambiguous profile types fail without warnings', authenticate($provider, array_replace($signed, ['first_name' => $value])) === null);
}

// Even a correctly signed scalar-looking representation must not validate another JSON type.
$payload = signPayload(array_replace($base, ['id' => '555.5']));
$payload['id'] = 555.5;
check('Signed floating-point identity is rejected', authenticate($provider, $payload) === null);
$payload = signPayload(array_replace($base, ['first_name' => '1']));
$payload['first_name'] = true;
check('Boolean profile value cannot reuse a string signature', authenticate($provider, $payload) === null);

$expired = signPayload(array_replace($base, ['auth_date' => time() - 86401]));
check('Existing one-day freshness limit remains enforced', authenticate($provider, $expired) === null);
$future = signPayload(array_replace($base, ['auth_date' => time() + 3600]));
check('Future timestamp policy is unchanged', authenticate($provider, $future) !== null);

foreach ([[], ['code' => null], ['code' => []], ['code' => ''], ['code' => 'invalid'], ['code' => base64_encode('null')], ['code' => base64_encode('42')], ['code' => base64_encode('{')]] as $options) {
    $validToken = authenticate($provider, $signed);
    check('Valid authentication succeeds before a subsequent failure', $validToken !== null);
    check('Malformed input fails cleanly', $provider->getAccessToken('authorization_code', $options) === null);
    check('Malformed attempt clears the earlier verified identity', hasNoIdentity($provider, $validToken));
}

foreach ([$expired, $signed + ['extra' => 'unsigned'], array_replace($signed, ['hash' => str_repeat('0', 64)])] as $payload) {
    $validToken = authenticate($provider, $signed);
    check('Failed authentication is rejected after a successful attempt', authenticate($provider, $payload) === null);
    check('Failed verification clears the earlier identity', hasNoIdentity($provider, $validToken));
}

check('Fresh provider has no unverified resource owner', hasNoIdentity(new Telegram([
    'clientId' => '123',
    'clientSecret' => BOT_SECRET,
    'redirectUri' => 'https://site.test/callback',
]), $token));
