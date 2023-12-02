<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\dropbox\provider\Dropbox as DropboxProvider;
use verbb\auth\models\Token;

class Dropbox extends DropboxProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.dropbox.com/2/';
    }
}