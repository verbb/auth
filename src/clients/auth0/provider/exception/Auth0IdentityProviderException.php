<?php
namespace verbb\auth\clients\auth0\provider\exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class Auth0IdentityProviderException extends IdentityProviderException
{
    /**
     * @param  ResponseInterface $response
     * @param string|null $message
     * @return IdentityProviderException
     */
    public static function fromResponse(ResponseInterface $response, string $message = null): IdentityProviderException
    {
        return new static($message, $response->getStatusCode(), (string) $response->getBody());
    }
}
