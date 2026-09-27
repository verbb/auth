# Usage
The Auth plugin is geared around OAuth 1 and OAuth 2 authentication workflows, so it's best to review how that works.

1. Generate an authorization URL
2. Redirect users to the offsite authorization URL, where they login to the provider platform
3. The platform redirects back to a callback endpoint that we nominate
4. We validate the callback and retrieve the authorization code
5. Make a token request with the authorization code and retrieve the access token

The Auth module takes care of steps 1, 2, 4, and 5. It will be up to your plugin to nominate a callback URL and trigger the Auth module to trigger the token request.

Let's run through an example from start to finish. We'll assume you're [working on a site module](https://verbb.io/blog/everything-you-need-to-know-about-modules), but this would also be applicable in a plugin.

## Local provider class
Before we dive in, we'll need to create our own provider class for our plugin. This implements an Auth provider class. Let's use Facebook as an example.

Create a `site-module/providers/Facebook.php` file with the following:

```php
<?php
namespace modules\sitemodule\providers;

use verbb\auth\base\OAuthProvider;
use verbb\auth\providers\Facebook as FacebookProvider;

class Facebook extends OAuthProvider
{
    // Public Methods
    // =========================================================================

    public function getOAuthProviderClass(): string
    {
        return FacebookProvider::class;
    }
}
```

Here, we extend the `OAuthProvider` class which abstracts some logic for dealing with OAuth providers. There's only one method we need to implement which is providing a Auth provider class to handle authentication. 

The idea is that you'll add to this class with your own provider logic depending on your plugin.

### Controller setup
In your plugin, you'll need to create two controller endpoints - one for initialising the request, the other to receive the request from the provider.

```php
<?php
namespace modules\sitemodule\controllers;

use Craft;
use craft\web\Controller;

use yii\web\Response;

class AuthController extends Controller
{
    // Properties
    // =========================================================================

    protected array|int|bool $allowAnonymous = ['login', 'callback'];


    // Public Methods
    // =========================================================================

    public function beforeAction($action): bool
    {
        // Don't require CSRF validation for callback requests
        if ($action->id === 'callback') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actionLogin(): Response
    {
        
    }

    public function actionCallback(): Response
    {
        
    }
}
```

Here, we've generated the skeleton of a controller for our two endpoints. We have two anonymous routes:
- `/actions/site-module/auth/login`
- `/actions/site-module/auth/callback`

The endpoints can be whatever you like, this is just the example we're using.

### Generate authorization URL
When triggering the `site-module/auth/login` controller action, we'll need to create the authorization URL for a provider, and redirect away to their site.

We can do this with the following:

```php
use verbb\auth\Auth;

public function actionLogin(): Response
{
    // Create the provider class with the redirectUri pointing to our `actionCallback` method
    $provider = new \modules\socialmodule\providers\Facebook([
        'clientId' => '••••••••••••••••••••••••••••',
        'clientSecret' => '••••••••••••••••••••••••••••',
        'redirectUri' => UrlHelper::actionUrl('site-module/auth/callback'),
        'graphApiVersion' => 'v3.3',
    ]);

    // Redirect to the provider platform to login and authorize
    return Auth::getInstance()->getOAuth()->connect('my-plugin-handle', $provider, 'some-reference');
}
```

We construct a config variable (which follows the standard [`league/oauth2-client`](https://oauth2-client.thephpleague.com/usage/) syntax), fetch a provider of our choosing (Facebook for example), and generate a authorization URL. With that set, we then redirect away with `connect()`.

Some providers need extra settings, like the Facebook OAuth provider needs the `graphApiVersion`.

### Callback
After the user logs in on the provider site, they'll be redirect back to our site, hitting the `site-module/auth/callback` controller action.

Let's add some code to handle generating the token.

```php
public function actionCallback(): Response
{
    $oauth = Auth::getInstance()->getOAuth();

    // For callback URLs on another host, establish browser continuity before leaving for the provider.
    if ($response = $oauth->prepareCallback('my-plugin-handle')) {
        return $response;
    }

    // Claim the short-lived, one-use transaction and restore its explicit callback context.
    $oauth->claimCallback('my-plugin-handle');

    // Create the provider class with the redirectUri pointing to our `actionCallback` method
    $provider = new \modules\socialmodule\providers\Facebook([
        'clientId' => '••••••••••••••••••••••••••••',
        'clientSecret' => '••••••••••••••••••••••••••••',
        'redirectUri' => UrlHelper::actionUrl('social-module/auth/callback'),
        'graphApiVersion' => 'v3.3',
    ]);

    // Fetch the Token model from the provider
    $token = $oauth->callback('my-plugin-handle', $provider, 'some-reference');

    // Record a referene
    $token->reference = 'some-reference';

    // Save it to the database
    Auth::getInstance()->getTokens()->upsertToken($token);

    // Redirect to somewhere
    return $this->redirect('/module');
}
```

Here, we grab our provider, claim the OAuth transaction, then call `callback()` to use the authentication code returned from the provider to fetch an access token. Pass the same stable reference to `connect()` and `callback()` so the transaction cannot be used for a different configured target. We then call `upsertToken()` to save this to the database, while also adding a `reference` for our uses. `upsertToken()` will either create a new token, or find an existing token with the same `ownerHandle`, `providerType`, `tokenType` and `reference`. We could call `saveToken()` but that would likely cause duplicates every time we run this callback.

OAuth transactions expire after 15 minutes, can be claimed once, and are bound to the browser that started them. Auth stores the callback context in the server cache so the flow does not depend on the PHP session surviving an off-site redirect. When the callback uses another host, `prepareCallback()` performs one first-party redirect through that host to establish the transaction cookie before continuing to the provider. Proxy callback URLs with a `return` parameter establish continuity on the returned-to host while keeping the proxy URL registered with the provider. This also supports Apple’s cross-site POST callback and keeps concurrent browser tabs isolated from one another.

If your callback needs values such as a provider handle, pass only those explicit values as the fourth `connect()` argument. `claimCallback()` restores them through Auth’s session helper for compatibility:

```php
return $oauth->connect('my-plugin-handle', $provider, $provider->handle, [
    'providerHandle' => $provider->handle,
]);
```

The former `Session::storeSession()` and permissive one-argument `Session::restoreSession()` snapshot workflow must not be used for new integrations. Existing callbacks should move to `prepareCallback()` and `claimCallback()` so Auth can enforce the owner, browser, provider and target boundaries.

You can see this can be improved be having somewhere central to store the provider config - but that'll be up to you to implement in your plugin.

### Summary
So what does Auth do to help with this overall process, rather than doing it youself?
- Provide single-line calls to generate authentication URL and fetch access tokens.
- Works for either OAuth 1 or 2 with consolidated handling.
- Stores a short-lived OAuth transaction before redirecting, including the validated return URLs, provider state, PKCE or OAuth 1 credentials, owner and configured target.
- Restores explicitly supplied callback context even when the PHP session does not survive the provider redirect.
- Fire a number of events before the authorization URL redirect and before/after access tokens.
- Saves saving the token to the database table.
- Handles creating or updating the token to the database, depending on your criteria
- Provides a single-line `request()` method to make API calls with a valid token.


### OAuth Provider
The `OAuthProvider` class (or the `OAuthProviderTrait`) contains useful logic for creating an OAuth-based provider. It handles normalizing logic between OAuth 1 and 2 providers.

The only requirement is to define a `getOAuthProviderClass()` method that returns the class of a league-based provider.

- `clientId` and `clientSecret` properties, with auto-env variable parsing
- `redirectUri` to set the redirect URI.
- Getting the OAuth version for the provider.
- `getOAuthProviderConfig()` to set the config used when creating the provider.
- `getOAuthProvider()` to get the provider instance.
- `getAuthorizationUrlOptions()` to set any URL params when creating the authorization URL.
- `getAuthorizationUrl()` the authorization URL.
- `getAccessToken()` to return the access token from an authorization request.
- `getToken()` to define logic on how to retrieve a token from the database, once created.
- `request()` to trigger an authenticated API request, based on the `getToken()` token.
