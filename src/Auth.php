<?php
namespace verbb\auth;

use verbb\auth\base\PluginTrait;

use Craft;
use craft\db\MigrationManager;

use verbb\base\base\Module;

class Auth extends Module
{
    // Constants
    // =========================================================================

    public const ID = 'verbb-auth';


    // Traits
    // =========================================================================

    use PluginTrait;


    // Properties
    // =========================================================================

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
            'track' => 'module:' . Auth::ID,
            'migrationNamespace' => 'verbb\\auth\\migrations',
            'migrationPath' => __DIR__ . DIRECTORY_SEPARATOR . 'migrations',
        ]);
    }
    
}