<?php
namespace verbb\auth\base;

use craft\helpers\ArrayHelper;

abstract class OAuthProvider implements OAuthProviderInterface
{
    // Traits
    // =========================================================================

    use OAuthProviderTrait;


    // Public Methods
    // =========================================================================

    public function __construct(array $config = [])
    {
        // Allow config to be set via the constructor, but move a few things out
        $this->clientId = ArrayHelper::remove($config, 'clientId');
        $this->clientSecret = ArrayHelper::remove($config, 'clientSecret');
        $this->redirectUri = ArrayHelper::remove($config, 'redirectUri');
        
        $this->config = $config;
    }

}
