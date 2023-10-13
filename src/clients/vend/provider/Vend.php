<?php

namespace verbb\auth\clients\vend\provider;

use GuzzleHttp\Client;
use verbb\auth\clients\vend\provider\exception\VendProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\vend\provider\vendapi\VendAPI;

use League\OAuth2\Client\Provider\AbstractProvider;
use Exception;
use InvalidArgumentException;

class Vend extends AbstractProvider
{
    public const VEND_AUTHORIZATION_URI = 'https://secure.vendhq.com/connect';
    public const VEND_API_URI = 'https://%s.vendhq.com';
    public const VEND_ACCESS_TOKEN_URI = 'https://%s.vendhq.com/api/1.0/token';

    /** @var string */
    protected mixed $storeName;

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (empty($options['storeName'])) {
            $message = 'The "storeName" option not set. Please set Store Name.';
            throw new InvalidArgumentException($message);
        }

        $this->storeName = $options['storeName'];
    }

    public function getBaseAuthorizationUrl(): string
    {
        return static::VEND_AUTHORIZATION_URI;
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return sprintf(self::VEND_ACCESS_TOKEN_URI, $this->getStoreName());
    }

    public function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getBaseVendApiUrl();
    }

    protected function createResourceOwner(array $response, AccessToken $token): null
    {
        return null;
    }

    public function vendApi(AccessToken $token): VendAPI
    {
        return new VendAPI($this->getBaseVendApiUrl(), 'Bearer', $token);
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions(): array
    {
        return [
            'urlAuthorize',
            'urlAccessToken',
        ];
    }

    /**
     * @param ResponseInterface $response
     * @param $data
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (!empty($data['error'])) {
            $message = $data['error'];
            $message = isset($data['error_description']) ? $message . ': '. $data['error_description'] : $message;
            throw new Exception($message);
        }
    }

    /**
     * Get the Vend api URL.
     *
     * @return string
     */
    protected function getBaseVendApiUrl(): string
    {
        return sprintf(self::VEND_API_URI, $this->getStoreName());
    }

    /**
     * Set the store name
     *
     * @param string $storeName
     */
    protected function setStoreName(string $storeName): void
    {
        $this->storeName = $storeName;
    }

    /**
     * Get the store name
     * @return string
     */
    protected function getStoreName(): string
    {
        return $this->storeName;
    }

    /**
     * Verifies that all required options have been passed.
     *
     * @param  array $options
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertRequiredOptions(array $options): void
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);
        if (!empty($missing)) {
            throw new InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }
}
