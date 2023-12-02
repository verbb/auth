<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\vkontakte\provider\Vkontakte as VkontakteProvider;
use verbb\auth\models\Token;

class Vkontakte extends VkontakteProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->baseUri;
    }
}