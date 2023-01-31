# Auth Module for Craft CMS
Auth is a module for Craft plugins and modules to making working with authenticating third-party APIs a breeze. We currently support OAuth1/OAuth2 providers and extend the [league/oauth1-client](https://github.com/thephpleague/oauth2-client) and [league/oauth2-client](https://github.com/thephpleague/oauth2-client) packages.

As such, this module is an opinionated wrapper around these packages that lower boilerplate code, provide a consistent API layer and tightly integrate with Craft itself.

**Heads up!** This isn't a Craft plugin that is intended for end-users. Instead, it's something you can install in your own Craft plugins or Craft modules to handle the intricacies of dealing with authentication.

## Installation
You can add the package to your project using Composer, or as a requirement in your `composer.json` file directly:

```shell
composer require verbb/auth
```

```json
"require": {
    "php": "^8.0.2",
    "craftcms/cms": "^4.0.0",
    "verbb/auth": "^1.0.0"
}
```

## Requirements
Auth requires Craft CMS 4.0+ and PHP 8.0.2+.

## Setup
There's a few things you'll need to do to get the Auth module working for your plugin.

### Initialize
In your plugin's `init()` function, you'll need to initialize the Auth module.

```php
public function init(): void
{
    parent::init();

    // Initialize the Auth module
    \verbb\auth\Auth::registerModule();

    // ...
}
```

With that done, you'll be able to access the methods in the Auth module.

### Migrations
Because the Auth plugin stores OAuth tokens in its own database table that's plugin-agnostic, you'll need to ensure that Auth's migration is run. In your plugin's `migrations\Install.php` file, add the following:

```php
class Install extends \craft\db\Migration
{
    public function safeUp(): bool
    {
        // Ensure that the Auth module kicks off setting up tables
        \verbb\auth\Auth::$plugin->migrator->up();

        // Create any tables that your plugin requires
        $this->createTables();

        // ...
    }
}
```

This will ensure that the Auth database tables are created (if they don't already exist from another plugin requiring it), ready for you to add tokens to.

That completes the setup side of things!


## Usage
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
        'clientId' => 'XXXXXX',
        'clientSecret' => 'XXXXXX',
        'redirectUri' => UrlHelper::actionUrl('site-module/auth/callback'),
        'graphApiVersion' => 'v3.3',
    ]);

    // Redirect to the provider platform to login and authorize
    return Auth::$plugin->getOAuth()->connect('my-plugin-handle', $provider);
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
    // Create the provider class with the redirectUri pointing to our `actionCallback` method
    $provider = new \modules\socialmodule\providers\Facebook([
        'clientId' => 'XXXXXX',
        'clientSecret' => 'XXXXXX',
        'redirectUri' => UrlHelper::actionUrl('social-module/auth/callback'),
        'graphApiVersion' => 'v3.3',
    ]);

    // Fetch the Token model from the provider
    $token = Auth::$plugin->getOAuth()->callback('my-plugin-handle', $provider);

    // Record a referene
    $token->reference = 'some-reference';

    // Save it to the database
    Auth::$plugin->getTokens()->upsertToken($token);

    // Redirect to somewhere
    return $this->redirect('/module');
}
```

Here, we grab our provider, then call `callback()` to use the authentication code returned from the provider to fetch an access token. We then call `upsertToken()` to save this to the database, while also adding a `reference` for our uses. `upsertToken()` will either create a new token, or find an existing token with the same `ownerHandle`, `providerType`, `tokenType` and `reference`. We could call `saveToken()` but that would likely cause duplicates every time we run this callback.

You can see this can be improved be having somewhere central to store the provider config - but that'll be up to you to implement in your plugin.

### Summary
So what does Auth do to help with this overall process, rather than doing it youself?
- Provide single-line calls to generate authentication URL and fetch access tokens.
- Works for either OAuth 1 or 2 with consolidated handling.
- Adds session variables before an authorization URL redirect to:
    - `redirect` to allow a redirect after hitting the callback endpoint.
    - `state` to validate the state returned by the authorization URL for CSRF protection.
    - `origin` to keep track of the referrer.
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


### Events

### The `beforeAuthorizationRedirect` event
The event that is triggered before a user is redirect to the provider.

```php
use verbb\auth\events\AuthorizationUrlEvent;
use verbb\auth\services\OAuth;
use yii\base\Event;

Event::on(OAuth::class, OAuth::EVENT_BEFORE_AUTHORIZATION_REDIRECT, function(AuthorizationUrlEvent $event) {
    $provider = $event->provider;
    $ownerHandle = $event->ownerHandle;
    $authUrl = $event->authUrl;
    // ...
});
```

### The `beforeFetchAccessToken` event
The event that is triggered before an access token is fetched from the provider.

```php
use verbb\auth\events\AccessTokenEvent;
use verbb\auth\services\OAuth;
use yii\base\Event;

Event::on(OAuth::class, OAuth::EVENT_BEFORE_FETCH_ACCESS_TOKEN, function(AccessTokenEvent $event) {
    $provider = $event->provider;
    $ownerHandle = $event->ownerHandle;
    // ...
});
```

### The `afterFetchAccessToken` event
The event that is triggered before an access token is fetched from the provider.

```php
use verbb\auth\events\AccessTokenEvent;
use verbb\auth\services\OAuth;
use yii\base\Event;

Event::on(OAuth::class, OAuth::EVENT_AFTER_FETCH_ACCESS_TOKEN, function(AccessTokenEvent $event) {
    $provider = $event->provider;
    $ownerHandle = $event->ownerHandle;
    $accessToken = $event->accessToken;
    $token = $event->token;
    // ...
});
```

### The `beforeSaveToken` event
The event that is triggered before a token is saved.

```php
use verbb\auth\events\TokenEvent;
use verbb\auth\services\Tokens;
use yii\base\Event;

Event::on(Tokens::class, Tokens::EVENT_BEFORE_SAVE_TOKEN, function(TokenEvent $event) {
    $token = $event->token;
    $isNew = $event->isNew;
    // ...
});
```

### The `afterSaveToken` event
The event that is triggered after a token is saved.

```php
use verbb\auth\events\TokenEvent;
use verbb\auth\services\Tokens;
use yii\base\Event;

Event::on(Tokens::class, Tokens::EVENT_AFTER_SAVE_TOKEN, function(TokenEvent $event) {
    $token = $event->token;
    $isNew = $event->isNew;
    // ...
});
```

### The `beforeDeleteToken` event
The event that is triggered before a token is deleted.

```php
use verbb\auth\events\TokenEvent;
use verbb\auth\services\Tokens;
use yii\base\Event;

Event::on(Tokens::class, Tokens::EVENT_BEFORE_DELETE_TOKEN, function(TokenEvent $event) {
    $token = $event->token;
    // ...
});
```

### The `afterDeleteToken` event
The event that is triggered after a token is deleted.

```php
use verbb\auth\events\TokenEvent;
use verbb\auth\services\Tokens;
use yii\base\Event;

Event::on(Tokens::class, Tokens::EVENT_AFTER_DELETE_TOKEN, function(TokenEvent $event) {
    $token = $event->token;
    // ...
});
```


## Database token storage
Auth takes care of storing and refreshing OAuth tokens in its own database table (`auth_oauth_tokens`), for all plugins and modules. This saves your plugin having to store and manage this yourself. There is a `Tokens` service for all the usual management of tokens from creation to saving and deletion. Each action should be carried out in the context of your own plugin (you wouldn't want to delete another plugin's token!).

### Owner
The owner handle is a reference to the "owner" of the token. This is either the Craft plugin handle, or the Craft module ID. This is to ensure that tokens for one plugin or module don't get mixed up with another.

### Provider Type
The class of the `provider` that this token belongs to.

### Token Type
Tokens keep track of the type of access token used to create it. Either `oauth1` or `oauth2`.

### Reference
The reference is a free-to-use field for your plugin to use to reference the token. This can be any value you like, and can be used if you'd like to keep track of what tokens are for what purpose in your plugin.

For example, the [Social Login](https://github.com/verbb/social-login) allows users to login or connect to social media accounts. It will record a Craft user ID against a token, so it can be used later for API requests. We store the User ID as a `reference` so we can fetch the token later.

### Examples

```php
use verbb\auth\Auth;

// Get all tokens for a plugin
$tokens = Auth::$plugin->getTokens()->getAllOwnerTokens('plugin-handle');

// Get all tokens for a plugin and reference
$tokens = Auth::$plugin->getTokens()->getAllTokensByOwnerReference('plugin-handle', 'myToken');

// Get the latest token for a plugin and reference
$token = Auth::$plugin->getTokens()->getTokenByOwnerReference('plugin-handle', 'myToken');

// Create a Token object from a provider and the returned OAuth access token
$token = Auth::$plugin->getTokens()->createToken('plugin-handle', $provider, $accessToken);

// For a given token, update or create it. This is useful when creating or updating tokens for a plugin, provider and reference
$success = Auth::$plugin->getTokens()->upsertToken($token);

// Refresh an access token (if the access token supports refreshing)
$success = Auth::$plugin->getTokens()->refreshToken($token, $accessToken);

// Saves a token
$success = Auth::$plugin->getTokens()->saveToken($token);

// Deletes a token
$success = Auth::$plugin->getTokens()->deleteToken($token);
```

## Provider anatomy
You'll see that we have a [clients]() folder that houses all the OAuth clients that extend from `League\OAuth2\Client\Provider\AbstractProvider`. These have been collected from various sources, or developed ourselves into this monorepo to allow mass-adoption of these providers in your own plugins. Otherwise, we'd need to include tens of different packages in an install, deal with conflicts, etc. These are now namespaced to `verbb\auth\clients\*` to convenience, but they shouldn't be used in your own modules.

All credit to the original authors of these clients.

Instead of referencing the client in your plugins, you should reference our Provider class. These are a thin layer over the client that provide some additional structure. These are namespaced to `verbb\auth\providers\*`, and extend the clients.

Providers add a compulsary `getBaseApiUrl()` function that returns a string for the base URL to the provider API. This is so we can create consistent HTTP clients for use in your plugins. Each provider class should either extend the `verbb\auth\base\Provider` class, or where not possible to extend, include the `verbb\auth\base\ProviderTrait`.

### Adding your own
You're more than welcome to submit a PR with another provider for us to support. You'll need to create a `client` folder of classes (as if you were submitting a package to `league/oauth2-client`) and a `provider` class. Take a look at the many examples for how to get started. Explaining the `league/oauth2-client` APIs are beyond the scope of this documentation.

## Supported providers
These providers extend any [league/oauth1-client](https://github.com/thephpleague/oauth2-client) or [league/oauth2-client](https://github.com/thephpleague/oauth2-client) packages, so if you would like to register your own, you can. Pull Requests are also most welcome to add support for any provider.

- [Amazon](https://amazon.com)
- [Apple](https://apple.com)
- [Auth0](https://auth0.com)
- [Authentiq](https://authentiq.com)
- [AWeber](https://aweber.com/)
- [Azure](https://azure.microsoft.com)
- [Basecamp](https://basecamp.com)
- [Bitbucket](https://bitbucket.com)
- [Box](https://box.com)
- [Buddy](https://buddy.works)
- [Buffer](https://buffer.com)
- [Constant Contact](https://constantcontact.com)
- [Deezer](https://deezer.com)
- [DeviantArt](https://deviantart.com)
- [Discord](https://discord.com)
- [Disqus](https://disqus.com)
- [DocuSign](https://docusign.com)
- [Dribbble](https://dribbble.com)
- [Drip](https://drip.com)
- [Dropbox](https://dropbox.com)
- [Envato](https://envato.com)
- [Etsy](https://etsy.com)
- [Eventbrite](https://eventbrite.com)
- [Facebook](https://facebook.com)
- [Fitbit](https://fitbit.com)
- [Foursquare](https://foursquare.com)
- [FreshBooks](https://freshbooks.com)
- [GitHub](https://github.com)
- [GitLab](https://gitlab.com)
- [Google](https://google.com)
- [GoToWebinar](https://goto.com/webinar)
- [Gumroad](https://gumroad.com)
- [Harvest](https://getharest.com)
- [Heroku](https://heroku.com)
- [HubSpot](https://hubspot.com)
- [Imgur](https://imgur.com)
- [Keap/Infusionsoft](https://keap.com)
- [Instagram](https://instagram.com)
- [Jira](https://jira.com)
- [Line](https://line.me)
- [LinkedIn](https://linkedin.com)
- [Linode](https://linode.com)
- [Mailchimp](https://mailchimp.com)
- [Mail.ru](https://mail.ru)
- [Marketo](https://marketo.com)
- [Mastodon](https://mastodon.social)
- [Meetup](https://meetup.com)
- [Microsoft](https://microsoft.com)
- [Mixer](https://mixer.app)
- [Mollie](https://mollie.com)
- [Myob](https://myob.com)
- [Odnoklassniki](https://ok.ru)
- [Okta](https://okta.com)
- [ORCID](https://orcid.org)
- [PayPal](https://paypal.com)
- [Pinterest](https://pinterest.com)
- [Pipedrive](https://pipedrive.com)
- [Reddit](https://reddit.com)
- [Salesforce](https://salesforce.com)
- [Shopify](https://shopify.com)
- [Slack](https://slack.com)
- [Snapchat](https://snapchat.com)
- [SoundCloud](https://soundcloud.com)
- [Spotify](https://spotify.com)
- [Square](https://squareup.com)
- [StackExchange](https://stackexchange.com)
- [Strava](https://strava.com)
- [Stripe](https://stripe.com)
- [SugarCRM](https://sugarcrm.com)
- [37signals](https://37signals.com)
- [TikTok](https://tiktok.com)
- [Trello](https://trello.com)
- [Trustpilot](https://trustpilot.com)
- [Tumblr](https://tumblr.com)
- [Twitch](https://twitch.tv)
- [Twitter](https://twitter.com)
- [Uber](https://uber.com)
- [Unsplash](https://unsplash.com)
- [Vend](https://vend.com)
- [Vimeo](https://vimeo.com)
- [Vkontakte](https://vk.com)
- [WeChat](https://wechat.com)
- [Weibo](https://weibo.cn)
- [Yahoo](https://yahoo.com)
- [Yelp](https://yelp.com)
- [Zendesk](https://zendesk.com)
- [Zoho](https://zoho.com)


## Plugins
You can see this plugin in action with [Social Login](https://github.com/verbb/social-login), [Social Feed](https://github.com/verbb/social-feed) and [Social Poster](https://github.com/verbb/social-poster).

## Credits
Thanks to all the individual developers working on `league/oauth2-client` packages.

## Show your Support
Auth is licensed under the MIT license, meaning it will always be free and open source – we love free stuff! If you'd like to show your support to the plugin regardless, [Sponsor](https://github.com/sponsors/verbb) development.

<h2></h2>

<a href="https://verbb.io" target="_blank">
    <img width="100" src="https://verbb.io/assets/img/verbb-pill.svg">
</a>
