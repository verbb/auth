<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\twitter\provider\Twitter as TwitterProvider;
use verbb\auth\helpers\Session;
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

    protected function getAuthorizationQuery(array $params): string
    {
        // Store PKCE token
        Session::set('oauth2verifier', $this->getPkceVerifier());

        return parent::getAuthorizationQuery($params);
    }

    protected function getAccessTokenRequest(array $params): RequestInterface
    {
        // Apply PKCE token
        $params['code_verifier'] = Session::get('oauth2verifier');

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