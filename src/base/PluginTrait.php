<?php
namespace verbb\auth\base;

use verbb\auth\Auth;
use verbb\auth\services\OAuth;
use verbb\auth\services\Tokens;

use Craft;

use verbb\base\LogTrait;
use verbb\base\helpers\Plugin;

trait PluginTrait
{
    // Static Methods
    // =========================================================================

    public static function bootstrap(): void
    {
        Auth::getInstance();
    }

    public static function getInstance(): Auth
    {
        if ($module = Craft::$app->getModule(Auth::ID)) {
            /** @var Auth $module */
            return $module;
        }

        $module = new Auth(Auth::ID);
        Auth::setInstance($module);
        Craft::$app->setModule(Auth::ID, $module);
        Craft::setAlias('@verbb/auth', __DIR__);

        return $module;
    }


    // Properties
    // =========================================================================

    public static ?Auth $plugin = null;


    // Traits
    // =========================================================================

    use LogTrait;


    // Public Methods
    // =========================================================================

    public function getOAuth(): OAuth
    {
        return $this->get('oauth');
    }

    public function getTokens(): Tokens
    {
        return $this->get('tokens');
    }


    // Private Methods
    // =========================================================================

    private function _registerComponents(): void
    {
        Plugin::bootstrapPlugin('auth');
        
        $this->setComponents([
            'oauth' => OAuth::class,
            'tokens' => Tokens::class,
        ]);
    }

}