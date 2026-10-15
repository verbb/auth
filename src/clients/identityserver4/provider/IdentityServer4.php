<?php
namespace verbb\auth\clients\identityserver4\provider;

use verbb\auth\clients\auth0\provider\Auth0;
use verbb\auth\helpers\ProviderError;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

use Psr\Http\Message\ResponseInterface;

class IdentityServer4 extends Auth0
{
    public function getBaseAccessTokenUrl(array $params = []): string
    {
        return $this->baseUrl() . '/token';
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (is_array($data) && isset($data['error'])) {
            throw new IdentityProviderException(
                ProviderError::message($data, $response->getReasonPhrase()),
                $response->getStatusCode(),
                $response
            );
        }
    }
}
