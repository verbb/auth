<?php
namespace verbb\auth;

use verbb\auth\base\PluginTrait;

use Craft;
use craft\db\MigrationManager;

use verbb\base\base\Module;

class Auth extends Module
{
    // Static Methods
    // =========================================================================

    public static function registerModule(): void
    {
        // Register this module as a Yii module, to be called in other plugins
        if (!Craft::$app->hasModule(self::$moduleId)) {
            Craft::$app->setModule(self::$moduleId, new Auth(self::$moduleId));

            Craft::$app->getModule(self::$moduleId);
        }
    }


    // Traits
    // =========================================================================

    use PluginTrait;


    // Properties
    // =========================================================================

    public static string $moduleId = 'verbb-auth';

    public string $handle = 'auth';
    public ?string $t9nCategory = 'auth';


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        $this->_registerComponents();

        // Setup a custom migrator track to allow us to use migrations in this module
        $this->set('migrator', [
            'class' => MigrationManager::class,
            'track' => 'module:' . self::$moduleId,
            'migrationNamespace' => 'verbb\\auth\\migrations',
            'migrationPath' => __DIR__ . DIRECTORY_SEPARATOR . 'migrations',
        ]);
    }
    
}