<?php

namespace verbb\auth\clients\yelp\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\yelp\tool\ClientCredentialsOnlyTrait;

class Yelp extends AbstractProvider
{
    use ClientCredentialsOnlyTrait;

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://api.yelp.com/oauth2/token';
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            throw new IdentityProviderException(
                $data['error']['description'] ?? $response->getReasonPhrase(),
                $statusCode,
                $response
            );
        }
    }
}
