<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\zendesk\provider\Zendesk as ZendeskProvider;
use verbb\auth\models\Token;

class Zendesk extends ZendeskProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://' . $this->subdomain . '.zendesk.com/api/v2/';
    }
}