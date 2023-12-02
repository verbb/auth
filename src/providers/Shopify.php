<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\shopify\provider\Shopify as ShopifyProvider;
use verbb\auth\models\Token;

class Shopify extends ShopifyProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://' . $this->shop . '/admin/';
    }
}