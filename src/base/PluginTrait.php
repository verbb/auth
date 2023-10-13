<?php
namespace verbb\auth\base;

use verbb\auth\Auth;
use verbb\auth\services\OAuth;
use verbb\auth\services\Tokens;

use verbb\base\LogTrait;
use verbb\base\helpers\Plugin;

trait PluginTrait
{
    // Properties
    // =========================================================================

    public static ?Auth $plugin = null;


    // Traits
    // =========================================================================

    use LogTrait;
    

    // Static Methods
    // =========================================================================

    public static function config(): array
    {
        Plugin::bootstrapPlugin('auth');

        return [
            'components' => [
                'oauth' => OAuth::class,
                'tokens' => Tokens::class,
            ],
        ];
    }


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

}