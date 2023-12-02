<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\freshbooks\provider\FreshBooks as FreshBooksProvider;
use verbb\auth\models\Token;

class FreshBooks extends FreshBooksProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.freshbooks.com/auth/api/v1';
    }
}