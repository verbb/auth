<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\imgur\provider\Imgur as ImgurProvider;
use verbb\auth\models\Token;

class Imgur extends ImgurProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.imgur.com/3';
    }
}