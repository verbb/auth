# Events
Auth provides a collection of events for extending its functionality. Modules and plugins can register event listeners, typically in their `init()` methods, to modify Auth’s behavior.

## The `beforeAuthorizationRedirect` event
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

## The `beforeFetchAccessToken` event
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

## The `afterFetchAccessToken` event
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

## The `beforeSaveToken` event
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

## The `afterSaveToken` event
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

## The `beforeDeleteToken` event
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

## The `afterDeleteToken` event
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
