<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\mailchimp\provider\Mailchimp as MailchimpProvider;
use verbb\auth\models\Token;

class Mailchimp extends MailchimpProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return null;
    }
}