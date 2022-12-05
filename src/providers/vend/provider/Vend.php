<?php

namespace verbb\auth\providers\vend\provider;

use GuzzleHttp\Client;
use verbb\auth\providers\vend\provider\exception\VendProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\providers\vend\provider\vendapi\VendAPI;

use League\OAuth2\Client\Provider\AbstractProvider;

class Vend extends AbstractProvider
{
    const VEND_AUTHORIZATION_URI = 'https://secure.vendhq.com/connect';
    const VEND_API_URI = 'https://%s.vendhq.com';
    const VEND_ACCESS_TOKEN_URI = 'https://%s.vendhq.com/api/1.0/token';

    /** @var string */
    protected $storeName;

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (empty($options['storeName'])) {
            $message = 'The "storeName" option not set. Please set Store Name.';
            throw new \InvalidArgumentException($message);
        }

        $this->storeName = $options['storeName'];
    }

    public function getBaseAuthorizationUrl()
    {
        return static::VEND_AUTHORIZATION_URI;
    }

    /**
     * @param array $params
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return sprintf(self::VEND_ACCESS_TOKEN_URI, $this->getStoreName());
    }

    public function getDefaultScopes()
    {
        return [];
    }

    /**
     * @param AccessToken $token
     * @return mixed
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getBaseVendApiUrl();
    }

    /**
     * @param array $response
     * @param AccessToken $token
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return null;
    }

    /**
     * @param array $response
     * @param AccessToken $token
     */
    public function vendApi(AccessToken $token)
    {
        return new VendAPI($this->getBaseVendApiUrl(), 'Bearer', $token);
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions()
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
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $message = $data['error'];
            $message = isset($data['error_description']) ? $message . ': '. $data['error_description'] : $message;
            throw new \Exception($message);
        }
    }

    /**
     * Get the Vend api URL.
     *
     * @return string
     */
    protected function getBaseVendApiUrl()
    {
        return sprintf(self::VEND_API_URI, $this->getStoreName());
    }

    /**
     * Set the store name
     * @param string $storeName
     */
    protected function setStoreName($storeName)
    {
        $this->storeName = $storeName;
    }

    /**
     * Get the store name
     * @return string
     */
    protected function getStoreName()
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
    private function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);
        if (!empty($missing)) {
            throw new InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }
}
