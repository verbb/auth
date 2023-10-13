<?php

namespace verbb\auth\clients\snapchat\provider\exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class SnapchatIdentityProviderException extends IdentityProviderException
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
        $message = $response->getReasonPhrase();
        $code = $response->getStatusCode();
        $body = (string) $response->getBody();

        if (isset($data['meta']['error_message'])) {
            $message = $data['meta']['error_message'];
        }
        if (isset($data['meta']['code'])) {
            $code = $data['meta']['code'];
        }

        return new static($message, $code, $body);
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
        $message = $response->getReasonPhrase();
        $code = $response->getStatusCode();
        $body = (string) $response->getBody();

        if (isset($data['error_message'])) {
            $message = $data['error_message'];
        }
        if (isset($data['code'])) {
            $code = $data['code'];
        }

        return new static($message, $code, $body);
    }
}