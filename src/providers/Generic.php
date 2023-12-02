<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\models\Token;

use League\OAuth2\Client\Provider\GenericProvider;

class Generic extends GenericProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Properties
    // =========================================================================

    protected ?string $baseApiUrl = null;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return $this->baseApiUrl;
    }

    public function getApiRequestQueryParams(?Token $token): array
    {
        return [
            'access_token' => (string)$token->getToken(),
        ];
    }
}