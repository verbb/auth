<?php

namespace verbb\auth\clients\harvest\provider\exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class HarvestIdentityProviderException extends IdentityProviderException
{
    /**
     * Creates client exception from response.
     *
     * @param  ResponseInterface $response
     * @param string $data Parsed response data
     *
     * @return IdentityProviderException
     */
    public static function clientException(ResponseInterface $response, string $data): IdentityProviderException
    {
        return static::fromResponse(
            $response,
            $data['message'] ?? $response->getReasonPhrase()
        );
    }

    /**
     * Creates oauth exception from response.
     *
     * @param  ResponseInterface $response
     * @param string $data Parsed response data
     *
     * @return IdentityProviderException
     */
    public static function oauthException(ResponseInterface $response, string $data): IdentityProviderException
    {
        return static::fromResponse(
            $response,
            $data['error'] ?? $response->getReasonPhrase()
        );
    }

    /**
     * Creates identity exception from response.
     *
     * @param  ResponseInterface $response
     * @param string|null $message
     *
     * @return IdentityProviderException
     */
    protected static function fromResponse(ResponseInterface $response, string $message = null): IdentityProviderException
    {
        return new static($message, $response->getStatusCode(), (string) $response->getBody());
    }
}
