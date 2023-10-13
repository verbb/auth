<?php

namespace verbb\auth\clients\gotowebinar\provider\exception;

use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class GotoWebinarProviderException extends IdentityProviderException {

    /**
     * @var int
     */
    private int $httpStatusCode;

    /**
     * @param string $message
     * @param int $code
     * @param array|string $body The response body
     */
    public function __construct($message, $code, $body) {
        $this->httpStatusCode = $code;
        parent::__construct($message, $code, $body);
    }

    /**
     * Creates client exception from response.
     *
     * @param  ResponseInterface $response
     * @param string $data Parsed response data
     *
     * @return GotoWebinarProviderException
     */
    public static function clientException(ResponseInterface $response, string $data): GotoWebinarProviderException
    {
        return static::fromResponse($response, $data['errorCode'] ?? $response->getReasonPhrase());
    }

    /**
     * Creates oauth exception from response.
     *
     * @param  ResponseInterface $response
     * @param string $data Parsed response data
     *
     * @return GotoWebinarProviderException
     */
    public static function oauthException(ResponseInterface $response, string $data): GotoWebinarProviderException
    {
        return static::fromResponse($response, $data['errorCode'] ?? $response->getReasonPhrase());
    }

    /**
     * Creates identity exception from response.
     *
     * @param  ResponseInterface $response
     * @param string|null $message
     *
     * @return GotoWebinarProviderException
     */
    protected static function fromResponse(ResponseInterface $response, string $message = null): GotoWebinarProviderException
    {
        return new static($message, $response->getStatusCode(), (string) $response->getBody());
    }

    /**
     * Generate a HTTP response.
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function generateHttpResponse(ResponseInterface $response): ResponseInterface
    {
        $headers = $this->getHttpHeaders();
        foreach ($headers as $header => $content) {
            $response = $response->withHeader($header, $content);
        }
        $response->getBody()->write($this->response);
        return $response->withStatus($this->getHttpStatusCode());
    }

    /**
     * All error response headers.
     *
     * @return array Array with header values
     */
    public function getHttpHeaders(): array
    {
        return [
            'Content-type' => 'application/json',
        ];
    }

    /**
     * Check if the exception has an associated redirect URI.
     *
     * @return bool
     */
    public function hasRedirect(): bool
    {
        return false;
    }

    /**
     * Returns the HTTP status code to send when the exceptions is output.
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

}
