<?php
namespace verbb\auth\clients\stackexchange\provider\exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class StackExchangeException extends IdentityProviderException
{
    public static function errorResponse(ResponseInterface $response, array $data): StackExchangeException
    {
        $message = $data['error']['type'];

        if (!empty($data['error']['message'])) {
            $message .= ': ' . $data['error']['message'];
        }

        $code = $response->getStatusCode();
        $body = (string) $response->getBody();

        return new static($message, $code, $body);
    }
}
