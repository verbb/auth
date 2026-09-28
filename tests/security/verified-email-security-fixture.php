<?php
$vendorDir = getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__, 2) . '/vendor';

require $vendorDir . '/autoload.php';
require __DIR__ . '/../../src/models/UserProfile.php';
require __DIR__ . '/../../src/clients/apple/token/AppleAccessToken.php';
require __DIR__ . '/../../src/clients/auth0/provider/Auth0ResourceOwner.php';
require __DIR__ . '/../../src/clients/discord/provider/DiscordResourceOwner.php';
require __DIR__ . '/../../src/clients/github/provider/exception/GitHubIdentityProviderException.php';
require __DIR__ . '/../../src/clients/github/provider/GitHub.php';
require __DIR__ . '/../../src/clients/github/provider/GitHubResourceOwner.php';
require __DIR__ . '/../../src/clients/salesforce/provider/SalesforceResourceOwner.php';

use verbb\auth\clients\auth0\provider\Auth0ResourceOwner;
use verbb\auth\clients\apple\token\AppleAccessToken;
use verbb\auth\clients\discord\provider\DiscordResourceOwner;
use verbb\auth\clients\github\provider\GitHub as GitHubProvider;
use verbb\auth\clients\github\provider\GitHubResourceOwner;
use verbb\auth\clients\salesforce\provider\SalesforceResourceOwner;
use verbb\auth\models\UserProfile;

function check(string $description, bool $condition): void
{
    if (!$condition) {
        throw new RuntimeException("Failed: $description");
    }

    echo "PASS: $description\n";
}

$trueValues = [true, 1, '1', 'true'];
$falseValues = [false, 0, '0', 'false'];

foreach ($trueValues as $value) {
    $profile = new UserProfile(new Auth0ResourceOwner(['sub' => 'one', 'email_verified' => $value]));
    check('explicit true email verification is accepted', $profile->getEmailVerified() === true);
}

foreach ($falseValues as $value) {
    $profile = new UserProfile(new Auth0ResourceOwner(['sub' => 'one', 'email_verified' => $value]));
    check('explicit false email verification is rejected', $profile->getEmailVerified() === false);
}

$profile = new UserProfile(new Auth0ResourceOwner(['sub' => 'one']));
check('missing email verification remains unknown', $profile->getEmailVerified() === null);

$profile = new UserProfile(new Auth0ResourceOwner(['sub' => 'one', 'email_verified' => 'yes']));
check('ambiguous email verification remains unknown', $profile->getEmailVerified() === null);

$profile = new UserProfile(new DiscordResourceOwner(['id' => 'one', 'verified' => true]));
check('Discord verified is normalized as email verification', $profile->getEmailVerified() === true);

$profile = new UserProfile(new GitHubResourceOwner(['id' => 1, 'email_verified' => true]));
check('GitHub verified primary email is normalized', $profile->getEmailVerified() === true);

$profile = new UserProfile(new GitHubResourceOwner(['id' => 1, 'email_verified' => null]));
check('GitHub unavailable email proof remains unknown', $profile->getEmailVerified() === null);

$github = new GitHubProvider([
    'clientId' => 'client',
    'clientSecret' => 'secret',
    'redirectUri' => 'https://example.com/callback',
]);
parse_str((string)parse_url($github->getAuthorizationUrl(), PHP_URL_QUERY), $githubQuery);
check('GitHub requests the user:email scope', ($githubQuery['scope'] ?? null) === 'user:email');

$profile = new UserProfile(new SalesforceResourceOwner([
    'user_id' => 'user',
    'email_verified' => true,
    'organization_id' => 'org',
]));
check('Salesforce email verification is normalized', $profile->getEmailVerified() === true);
check('Salesforce organization is exposed for tenant policy', $profile->organizationId === 'org');

putenv('RANDFILE=' . sys_get_temp_dir() . '/verbb-auth-openssl.rnd');
$privateKey = openssl_pkey_new([
    'digest_alg' => 'sha256',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
]);
openssl_pkey_export($privateKey, $privatePem);
$details = openssl_pkey_get_details($privateKey);
$base64Url = fn(string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
$keySet = Firebase\JWT\JWK::parseKeySet(['keys' => [[
    'kty' => 'RSA',
    'kid' => 'fixture',
    'use' => 'sig',
    'alg' => 'RS256',
    'n' => $base64Url($details['rsa']['n']),
    'e' => $base64Url($details['rsa']['e']),
]]]);
$claims = [
    'iss' => 'https://appleid.apple.com',
    'aud' => 'com.example.app',
    'sub' => 'apple-user',
    'iat' => time(),
    'exp' => time() + 300,
    'email' => 'user@example.com',
    'email_verified' => 'false',
];
$idToken = Firebase\JWT\JWT::encode($claims, $privatePem, 'RS256', 'fixture');
$token = new AppleAccessToken($keySet, ['access_token' => 'access', 'id_token' => $idToken], 'com.example.app');
check('Apple false-like verification is rejected', $token->getEmailVerified() === false);
check('Apple does not trust an email with false verification', $token->getEmail() === null);

$claims['email_verified'] = 'true';
$idToken = Firebase\JWT\JWT::encode($claims, $privatePem, 'RS256', 'fixture');
$token = new AppleAccessToken($keySet, ['access_token' => 'access', 'id_token' => $idToken], 'com.example.app');
check('Apple accepts a strictly verified signed-token email', $token->getEmailVerified() === true && $token->getEmail() === 'user@example.com');

try {
    new AppleAccessToken($keySet, ['access_token' => 'access', 'id_token' => $idToken], 'com.other.app');
    check('Apple audience mismatch is rejected', false);
} catch (UnexpectedValueException) {
    check('Apple audience mismatch is rejected', true);
}
