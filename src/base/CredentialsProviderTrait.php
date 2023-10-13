<?php
namespace verbb\auth\base;

use Craft;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;

trait CredentialsProviderTrait
{
    // Public Methods
    // =========================================================================

    public function getCredentialsProviderConfig(): array
    {
        return [];
    }

    public function getCredentialsProviderOptions(array $options = []): array
    {
        return $options;
    }

    public function getCredentialsProvider(): Client
    {
        return Craft::createGuzzleClient($this->getCredentialsProviderConfig());
    }

    public function request(string $method = 'GET', string $uri = '', array $options = []): ResponseInterface
    {
        // Get the Guzzle provider
        $credentialsProvider = $this->getCredentialsProvider();

        // Allow providers to modify the options (add variables, query string, etc)
        $options = $this->getCredentialsProviderOptions($options);

        // Normalise the URI
        $uri = ltrim($uri, '/');

        return $credentialsProvider->request($method, $uri, $options);
    }
}
