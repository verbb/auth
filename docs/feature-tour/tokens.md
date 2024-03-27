# Tokens
Auth takes care of storing and refreshing OAuth tokens in its own database table (`auth_oauth_tokens`), for all plugins and modules. This saves your plugin having to store and manage this yourself. There is a `Tokens` service for all the usual management of tokens from creation to saving and deletion. Each action should be carried out in the context of your own plugin (you wouldn't want to delete another plugin's token!).

## Owner
The owner handle is a reference to the "owner" of the token. This is either the Craft plugin handle, or the Craft module ID. This is to ensure that tokens for one plugin or module don't get mixed up with another.

## Provider Type
The class of the `provider` that this token belongs to.

## Token Type
Tokens keep track of the type of access token used to create it. Either `oauth1` or `oauth2`.

## Reference
The reference is a free-to-use field for your plugin to use to reference the token. This can be any value you like, and can be used if you'd like to keep track of what tokens are for what purpose in your plugin.

For example, the [Social Login](https://github.com/verbb/social-login) allows users to login or connect to social media accounts. It will record a Craft user ID against a token, so it can be used later for API requests. We store the User ID as a `reference` so we can fetch the token later.

## Examples

```php
use verbb\auth\Auth;

// Get all tokens for a plugin
$tokens = Auth::getInstance()->getTokens()->getAllOwnerTokens('plugin-handle');

// Get all tokens for a plugin and reference
$tokens = Auth::getInstance()->getTokens()->getAllTokensByOwnerReference('plugin-handle', 'myToken');

// Get the latest token for a plugin and reference
$token = Auth::getInstance()->getTokens()->getTokenByOwnerReference('plugin-handle', 'myToken');

// Create a Token object from a provider and the returned OAuth access token
$token = Auth::getInstance()->getTokens()->createToken('plugin-handle', $provider, $accessToken);

// For a given token, update or create it. This is useful when creating or updating tokens for a plugin, provider and reference
$success = Auth::getInstance()->getTokens()->upsertToken($token);

// Refresh an access token (if the access token supports refreshing)
$success = Auth::getInstance()->getTokens()->refreshToken($token, $accessToken);

// Saves a token
$success = Auth::getInstance()->getTokens()->saveToken($token);

// Deletes a token
$success = Auth::getInstance()->getTokens()->deleteToken($token);
```