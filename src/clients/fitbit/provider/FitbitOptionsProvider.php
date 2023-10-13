<?php

namespace verbb\auth\clients\fitbit\provider;

use League\OAuth2\Client\OptionProvider\PostAuthOptionProvider;

class FitbitOptionsProvider extends PostAuthOptionProvider
{
    /**
     * The fitbit client id
     * @var string
     */
    private string $clientId;

    /**
     * the fitbit client secret
     * @var string
     */
    private string $clientSecret;

    /**
     * Set the client id and secret
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Builds request options used for requesting an access token.
     *
     * @param string $method
     * @param  array $params
     * @return array
     */
    public function getAccessTokenOptions($method, array $params): array
    {
        $options = parent::getAccessTokenOptions($method, $params);
        $options['headers']['Authorization'] =
            'Basic '.base64_encode($this->clientId.':'.$this->clientSecret);

        return $options;
    }
}
