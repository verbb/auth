# Events

Auth provides events for extending its functionality. Modules and plugins can register event listeners, typically in their `init()` methods, to change Auth’s behaviour.

## OAuth Events

### The `beforeAuthorizationRedirect` Event
The event that is triggered before the user is redirected to the provider to authorise the connection.

The event’s `authUrl` property holds the provider’s authorisation URL. Change it to add or adjust query parameters, and Auth redirects to the new value. The event can’t cancel the redirect.

```php
use verbb\auth\events\AuthorizationUrlEvent;
use verbb\auth\services\OAuth;
use yii\base\Event;

Event::on(OAuth::class, OAuth::EVENT_BEFORE_AUTHORIZATION_REDIRECT, function(AuthorizationUrlEvent $event) {
    $provider = $event->provider;
    $ownerHandle = $event->ownerHandle;

    $event->authUrl .= '&prompt=consent';
});
```

### The `beforeFetchAccessToken` Event
The event that is triggered after the provider redirects back, before Auth exchanges the authorisation code for an access token.

The event holds the `provider` and `ownerHandle`. It can’t cancel the request for the access token.

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

### The `afterFetchAccessToken` Event
The event that is triggered after Auth fetches an access token from the provider and creates a token model from it.

The event’s `accessToken` property holds the provider’s raw access token, and `token` holds Auth’s token model. The token isn’t saved yet, and `OAuth::callback()` returns the event’s `token`, so a listener can change its values or replace it.

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

## Token Events

### The `beforeSaveToken` Event
The event that is triggered before a token is saved.

The listener can change the token’s values before they’re validated and saved. Setting `$event->isValid` has no effect, so the event can’t prevent the save.

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

### The `afterSaveToken` Event
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

### The `beforeDeleteToken` Event
The event that is triggered before a token is deleted.

The event can’t prevent the deletion.

```php
use verbb\auth\events\TokenEvent;
use verbb\auth\services\Tokens;
use yii\base\Event;

Event::on(Tokens::class, Tokens::EVENT_BEFORE_DELETE_TOKEN, function(TokenEvent $event) {
    $token = $event->token;
    // ...
});
```

### The `afterDeleteToken` Event
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

### The `tokenRefreshFailed` Event
The event that is triggered when the provider permanently rejects a token refresh, such as Google returning `invalid_grant` because the refresh token was revoked or expired.

The event’s `token` property holds the stored token and `exception` holds the error from the provider. Auth deletes the token after this event and throws an `OAuthTokenRefreshException`, so the owner has no stored token until they reconnect. Use the event to prompt the owner to reconnect.

```php
use verbb\auth\events\TokenEvent;
use verbb\auth\services\Tokens;
use yii\base\Event;

Event::on(Tokens::class, Tokens::EVENT_TOKEN_REFRESH_FAILED, function(TokenEvent $event) {
    $token = $event->token;
    $exception = $event->exception;
    // Prompt a reconnect for $token->ownerHandle and $token->reference
});
```
