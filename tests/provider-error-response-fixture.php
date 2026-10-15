<?php
require (getenv('VERBB_AUTH_TEST_VENDOR') ?: dirname(__DIR__) . '/vendor') . '/autoload.php';

// Test this checkout, not an installed copy of Auth supplied by the dependency autoloader.
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
use verbb\auth\helpers\ProviderError;

set_error_handler(static function(int $severity, string $message, string $file, int $line): never {
    throw new ErrorException($message, 0, $severity, $file, $line);
}, E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE);

$checks = 0;
$failures = [];

function check(string $label, bool $condition): void
{
    global $checks, $failures;
    $checks++;

    if (!$condition) {
        $failures[] = $label;
    }
}

$providers = [
    \verbb\auth\clients\microsoft\provider\Microsoft::class,
    \verbb\auth\clients\aweber\provider\Aweber::class,
    \verbb\auth\clients\freeagent\provider\FreeAgent::class,
    \verbb\auth\clients\identityserver4\provider\IdentityServer4::class,
    \verbb\auth\clients\neoncrm\provider\NeonCrm::class,
    \verbb\auth\clients\onecrm\provider\OneCrm::class,
    \verbb\auth\clients\linkedin\provider\LinkedIn::class,
    \verbb\auth\clients\mailchimp\provider\Mailchimp::class,
    \verbb\auth\clients\zendesk\provider\Zendesk::class,
    \verbb\auth\clients\infusionsoft\provider\Infusionsoft::class,
    \verbb\auth\clients\auth0\provider\Auth0::class,
    \verbb\auth\clients\apple\provider\Apple::class,
    \verbb\auth\clients\dropbox\provider\Dropbox::class,
    \verbb\auth\clients\deviantart\provider\DeviantArt::class,
    \verbb\auth\clients\dribbble\provider\Dribbble::class,
    \verbb\auth\clients\stripe\provider\Stripe::class,
    \verbb\auth\clients\xero\provider\Xero::class,
    \verbb\auth\clients\snapchat\provider\Snapchat::class,
    \verbb\auth\clients\amazoncognito\provider\AmazonCognito::class,
    \verbb\auth\clients\zoho\provider\Zoho::class,
    \verbb\auth\clients\basecamp\provider\Basecamp::class,
    \verbb\auth\clients\meetup\provider\Meetup::class,
    \verbb\auth\clients\trustpilot\provider\Trustpilot::class,
    \verbb\auth\clients\tumblr\provider\Tumblr::class,
    \verbb\auth\clients\paypal\provider\PayPal::class,
    \verbb\auth\clients\google\provider\Google::class,
    \verbb\auth\clients\line\provider\Line::class,
    \verbb\auth\clients\okta\provider\Okta::class,
    \verbb\auth\clients\authentiq\provider\Authentiq::class,
    \verbb\auth\clients\yahoo\provider\Yahoo::class,
    \verbb\auth\clients\disqus\provider\Disqus::class,
    \verbb\auth\clients\vimeo\provider\Vimeo::class,
    \verbb\auth\clients\docusign\provider\Docusign::class,
    \verbb\auth\clients\salesforce\provider\Salesforce::class,
    \verbb\auth\providers\Twitter::class,
    \verbb\auth\clients\twitter\provider\Twitter::class,
    \verbb\auth\clients\constantcontact\provider\ConstantContact::class,
    \verbb\auth\clients\drip\provider\Drip::class,
    \verbb\auth\clients\shopify\provider\Shopify::class,
    \verbb\auth\clients\square\provider\Square::class,
    \verbb\auth\clients\github\provider\GitHub::class,
    \verbb\auth\clients\gitlab\provider\GitLab::class,
    \verbb\auth\clients\jira\provider\Jira::class,
    \verbb\auth\clients\etsy\provider\Etsy::class,
    \verbb\auth\clients\harvest\provider\Harvest::class,
    \verbb\auth\clients\gumroad\provider\Gumroad::class,
    \verbb\auth\clients\gotowebinar\provider\GotoWebinar::class,
];

$oauth = ['error' => 'invalid_grant', 'error_description' => 'Fixture token was revoked.'];
$expected = 'invalid_grant: Fixture token was revoked.';
$body = json_encode($oauth, JSON_THROW_ON_ERROR);
$arrayBodies = ['Google', 'Line', 'Okta', 'Authentiq', 'Yahoo', 'Disqus', 'Vimeo', 'ConstantContact', 'Drip', 'Shopify', 'Square', 'Twitter', 'Trustpilot', 'Tumblr', 'Meetup', 'AmazonCognito'];
$stringBodies = ['Auth0', 'GitHub', 'GitLab', 'Jira', 'Etsy', 'Harvest', 'Gumroad', 'GotoWebinar'];
$zeroCodes = ['Google', 'Line', 'Authentiq', 'Yahoo', 'Disqus', 'Vimeo', 'AmazonCognito', 'ConstantContact', 'Drip', 'Shopify', 'Square', 'Meetup'];

foreach ($providers as $class) {
    $reflection = new ReflectionClass($class);
    $provider = $reflection->newInstanceWithoutConstructor();
    $name = $reflection->getShortName();
    $method = $reflection->getMethod('checkResponse');
    $response = new Response(400, ['Content-Type' => 'application/json'], $body);

    foreach ([$oauth, ['error' => 'invalid_grant']] as $data) {
        try {
            $method->invoke($provider, $response, $data);
            check($class . ' rejects OAuth errors', false);
        } catch (IdentityProviderException $exception) {
            check($class . ' reports OAuth details', $exception->getMessage() === (isset($data['error_description']) ? $expected : 'invalid_grant'));
            check($class . ' retains exception code', $exception->getCode() === (in_array($name, $zeroCodes, true) ? 0 : 400));
            $expectedBody = match (true) {
                in_array($name, $arrayBodies, true) => $data,
                in_array($name, $stringBodies, true) => $body,
                $name === 'Docusign' => 'Bad Request',
                $name === 'DeviantArt' => $response->getBody(),
                default => $response,
            };
            check($class . ' retains response evidence', $exception->getResponseBody() === $expectedBody);
        } catch (Throwable $exception) {
            check($class . ': ' . $exception::class . ': ' . $exception->getMessage(), false);
        }

        $response->getBody()->rewind();
    }

    try {
        $method->invoke($provider, new Response(200), ['access_token' => 'fixture', 'token_type' => 'Bearer']);
        check($class . ' accepts successful responses', true);
    } catch (Throwable $exception) {
        check($class . ' accepts successful responses: ' . $exception->getMessage(), false);
    }
}

// Some providers signal OAuth errors in a successful HTTP response.
foreach ([
    \verbb\auth\clients\google\provider\Google::class,
    \verbb\auth\clients\github\provider\GitHub::class,
    \verbb\auth\clients\gitlab\provider\GitLab::class,
    \verbb\auth\clients\jira\provider\Jira::class,
    \verbb\auth\clients\etsy\provider\Etsy::class,
    \verbb\auth\clients\harvest\provider\Harvest::class,
    \verbb\auth\clients\gotowebinar\provider\GotoWebinar::class,
] as $class) {
    $reflection = new ReflectionClass($class);

    try {
        $reflection->getMethod('checkResponse')->invoke($reflection->newInstanceWithoutConstructor(), new Response(200), $oauth);
        check($class . ' rejects OAuth errors on HTTP 200', false);
    } catch (IdentityProviderException $exception) {
        check($class . ' reports OAuth errors on HTTP 200', $exception->getMessage() === $expected);
    } catch (Throwable $exception) {
        check($class . ' HTTP 200 error: ' . $exception->getMessage(), false);
    }
}

// Preserve provider-specific API formats in addition to standard OAuth token errors.
$apiCases = [
    [\verbb\auth\clients\microsoft\provider\Microsoft::class, ['error' => ['message' => 'API denied', 'code' => 'Forbidden']], 'API denied', 400],
    [\verbb\auth\clients\google\provider\Google::class, ['error' => ['message' => 'API denied', 'code' => 403]], 'API denied', 403],
    [\verbb\auth\clients\yahoo\provider\Yahoo::class, ['error' => ['description' => 'API access denied']], 'API access denied', 400],
    [\verbb\auth\clients\zendesk\provider\Zendesk::class, ['description' => 'Requester name is required'], 'Requester name is required', 400],
    [\verbb\auth\clients\mailchimp\provider\Mailchimp::class, ['description' => 'Account access denied'], 'Account access denied', 400],
    [\verbb\auth\clients\constantcontact\provider\ConstantContact::class, [['error_key' => 'bad_request', 'error_message' => 'Invalid contact']], 'Invalid contact', 400],
    [\verbb\auth\clients\drip\provider\Drip::class, ['errors' => ['Invalid subscriber']], 'Invalid subscriber', 400],
    [\verbb\auth\clients\shopify\provider\Shopify::class, ['errors' => 'Invalid shop'], 'Invalid shop', 400],
    [\verbb\auth\clients\square\provider\Square::class, ['errors' => [['category' => 'AUTHENTICATION_ERROR', 'code' => 'UNAUTHORIZED', 'detail' => 'Invalid access token']]], 'Invalid access token', 400],
    [\verbb\auth\clients\gotowebinar\provider\GotoWebinar::class, ['errorCode' => 'InvalidToken'], 'InvalidToken', 400],
];

foreach ($apiCases as [$class, $data, $message, $status]) {
    $reflection = new ReflectionClass($class);

    try {
        $reflection->getMethod('checkResponse')->invoke($reflection->newInstanceWithoutConstructor(), new Response($status), $data);
        check($class . ' rejects API errors', false);
    } catch (IdentityProviderException $exception) {
        check($class . ' preserves API messages', $exception->getMessage() === $message);
    } catch (Throwable $exception) {
        check($class . ' API response: ' . $exception->getMessage(), false);
    }
}

foreach ([null, false, '', '<html>upstream error</html>', new stdClass(), ['error_description' => [], 'error' => new stdClass()], ['message' => " \t\n"]] as $data) {
    check('Malformed messages use the HTTP fallback', ProviderError::message($data, 'Bad Request') === 'Bad Request');
}

check('Blank descriptions fall back to the error code', ProviderError::message(['error_description' => ' ', 'error' => 'invalid_client'], 'Bad Request') === 'invalid_client');
check('Non-message response fields are never dumped', ProviderError::message(['access_token' => 'secret', 'request' => ['client_secret' => 'secret']], 'Bad Request') === 'Bad Request');
check('Nested malformed message fields are ignored', ProviderError::message(['error' => ['message' => []], 'message' => 'Readable'], 'Bad Request') === 'Readable');

// Use League's actual response decoding path with a mocked HTTP client.
foreach ([
    \verbb\auth\clients\microsoft\provider\Microsoft::class,
    \verbb\auth\clients\google\provider\Google::class,
    \verbb\auth\clients\docusign\provider\Docusign::class,
    \verbb\auth\clients\constantcontact\provider\ConstantContact::class,
    \verbb\auth\clients\paypal\provider\PayPal::class,
] as $class) {
    try {
        $provider = new $class(['clientId' => 'fixture', 'clientSecret' => 'fixture']);
        $provider->setHttpClient(new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(400, ['Content-Type' => 'application/json'], $body),
        ]))]));
        $provider->getAccessToken('refresh_token', ['refresh_token' => 'fixture']);
        check($class . ' token exchange rejects', false);
    } catch (IdentityProviderException $exception) {
        check($class . ' token exchange retains code and description', $exception->getMessage() === $expected);
    } catch (Throwable $exception) {
        check($class . ' token exchange: ' . $exception->getMessage(), false);
    }
}

// Auth's reconnect decision must still recognize the enriched message.
$refreshProvider = (new ReflectionClass(\verbb\auth\providers\Google::class))->newInstanceWithoutConstructor();
$invalidGrant = new ReflectionMethod($refreshProvider, '_isInvalidGrant');
check('Enriched invalid_grant still requires reconnect', $invalidGrant->invoke($refreshProvider, $expected) === true);
check('Other enriched errors do not require reconnect', $invalidGrant->invoke($refreshProvider, 'invalid_client: Client authentication failed.') === false);

foreach ($failures as $failure) {
    fwrite(STDERR, "FAIL: {$failure}\n");
}

fwrite(STDOUT, sprintf("%d checks, %d failures across %d provider paths.\n", $checks, count($failures), count($providers)));
exit($failures === [] ? 0 : 1);
