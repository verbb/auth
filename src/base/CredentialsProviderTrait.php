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

        // Normalize the Base URI if we provide a URI. Sometimes, we may provide the "Base URI" as
        // the full URL for the request which can throw an error if we add a trailing slash
        if ($uri) {
            // We can't modify Guzzle client options, so re-make it
            $config = $credentialsProvider->getConfig();

            if (isset($config['base_uri']) && !str_ends_with($config['base_uri'], '/')) {
                $config['base_uri'] .= '/';
            }

            // Guzzle doesn't support modifying config, so create again
            $credentialsProvider = Craft::createGuzzleClient($config);
        }

        return $credentialsProvider->request($method, $uri, $options);
    }
}
