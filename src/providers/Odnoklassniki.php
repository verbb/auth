<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\odnoklassniki\provider\Odnoklassniki as OdnoklassnikiProvider;
use verbb\auth\models\Token;

class Odnoklassniki extends OdnoklassnikiProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(): ?string
    {
        return 'http://api.odnoklassniki.ru/fb.do';
    }

    public function getApiRequestQueryParams(Token $token): array
    {
        $sign = md5(str_replace('&', '', $param) . md5((string)$token->getToken() . $this->clientSecret));

        return [
            'access_token' => (string)$token->getToken(),
            'sig' => $sign,
        ];
    }
}