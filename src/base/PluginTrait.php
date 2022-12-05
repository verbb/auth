<?php
namespace verbb\auth\base;

use verbb\auth\Auth;
use verbb\auth\services\OAuth;
use verbb\auth\services\Providers;
use verbb\auth\services\Tokens;

use Craft;

use yii\log\Logger;

use verbb\base\BaseHelper;

trait PluginTrait
{
    // Static Properties
    // =========================================================================

    public static Auth $plugin;


    // Public Methods
    // =========================================================================

    public static function log($message, $attributes = []): void
    {
        if ($attributes) {
            $message = Craft::t('verbb-auth', $message, $attributes);
        }

        Craft::getLogger()->log($message, Logger::LEVEL_INFO, 'verbb-auth');
    }

    public static function error($message, $attributes = []): void
    {
        if ($attributes) {
            $message = Craft::t('verbb-auth', $message, $attributes);
        }

        Craft::getLogger()->log($message, Logger::LEVEL_ERROR, 'verbb-auth');
    }


    // Public Methods
    // =========================================================================

    public function getOAuth(): OAuth
    {
        return $this->get('oauth');
    }

    public function getProviders(): Providers
    {
        return $this->get('providers');
    }

    public function getTokens(): Tokens
    {
        return $this->get('tokens');
    }


    // Private Methods
    // =========================================================================

    private function _registerComponents(): void
    {
        $this->setComponents([
            'oauth' => OAuth::class,
            'providers' => Providers::class,
            'tokens' => Tokens::class,
        ]);
    }

    private function _registerLogTarget(): void
    {
        BaseHelper::setFileLogging('verbb-auth');
    }

}