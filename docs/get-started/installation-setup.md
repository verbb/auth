# Installation & Setup

## Installation
You can add the package to your project using Composer, or as a requirement in your `composer.json` file directly:

```shell
composer require verbb/auth
```

```json
"require": {
    "craftcms/cms": "^5.0.0",
    "verbb/auth": "^2.0.0"
}
```

## Setup
To use the Auth module in your plugin, just call `Auth::getInstance()` and any service or function you require.

```php
public function init(): void
{
    parent::init();

    // For example, connecting your "my-plugin" plugin's provider. 
    // Provider being a class that includes `OAuthProviderTrait` or implements `OAuthProviderInterface`
    \verbb\auth\Auth::getInstance()->getOAuth()->connect('my-plugin', $providerInstance);

    // Or, getting all stored tokens for your plugin
    \verbb\auth\Auth::getInstance()->getTokens()->getAllOwnerTokens('my-plugin');

    // ...
}
```

### Migrations
Because the Auth plugin stores OAuth tokens in its own database table that's plugin-agnostic, you'll need to ensure that Auth's migration is run. In your plugin's `migrations\Install.php` file, add the following:

```php
class Install extends \craft\db\Migration
{
    public function safeUp(): bool
    {
        // Ensure that the Auth module kicks off setting up tables
        \verbb\auth\Auth::getInstance()->migrator->up();

        // Create any tables that your plugin requires
        $this->createTables();

        // ...
    }
}
```

This will ensure that the Auth database tables are created (if they don't already exist from another plugin requiring it), ready for you to add tokens to.

If you're including this in a module, you'll not be able to make use of the `migrations\Install.php` migration that plugins have access to. Instead, you'll want to call this through a [content migration](https://craftcms.com/docs/5.x/extend/migrations.html).

That completes the setup side of things!