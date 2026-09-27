<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\twitter\provider\Twitter as TwitterProvider;
use verbb\auth\models\Token;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class Twitter extends TwitterProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.twitter.com/2/';
    }


    // Protected Methods
    // =========================================================================

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        if (!isset($params['code_verifier'])) {
            $params['code_verifier'] = $this->getPkceVerifier();
        }

        return parent::getAccessTokenRequest($params);
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        // Twitter often responds with 201, so the current check of `== 200` isn't going to work.
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            $error = $data['error_description'] ?? '';
            $code = $data['code'] ?? $statusCode;

            throw new IdentityProviderException($error, $code, $data);
        }
    }
}
