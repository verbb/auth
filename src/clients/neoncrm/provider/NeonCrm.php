<?php
namespace verbb\auth\clients\neoncrm\provider;

use verbb\auth\helpers\ProviderError;

use GuzzleHttp\Client as HttpClient;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

class NeonCrm extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected $organizationId;
    protected $apiKey;

    public function getBaseAuthorizationUrl(): string
    {
        return $this->baseUrl() . '/np/oauth/auth';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->baseUrl() . '/np/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.neoncrm.com/v2/accounts/' . $token->getToken();
    }

    public function getResponse(RequestInterface $request): ResponseInterface
    {
        if ((string)$request->getUri() !== $this->getBaseAccessTokenUrl([])) {
            return parent::getResponse($request);
        }

        // Neon redirects the token endpoint for organizations with a custom domain, and Guzzle would
        // otherwise follow that as an empty GET, which Neon rejects. Neon has already received the
        // credentials in the first request, so its redirect only needs to be held to HTTPS.
        return $this->getHttpClient()->send($request, [
            'allow_redirects' => [
                'strict' => true,
                'protocols' => ['https'],
            ],
        ]);
    }

    protected function baseUrl(): string
    {
        return 'https://' . $this->organizationId . '.app.neoncrm.com';
    }

    protected function getDefaultScopes(): array
    {
        return ['openid'];
    }

    protected function createResourceOwner(array $response, AccessToken $token): NeonCrmResourceOwner
    {
        return new NeonCrmResourceOwner($response);
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                ProviderError::message($data, $response->getReasonPhrase()),
                $response->getStatusCode(),
                $response
            );
        }

        // Neon also reports failures in its own format, which can arrive as a 200 response.
        if (($data['success'] ?? null) === false) {
            throw new IdentityProviderException(
                ProviderError::message($data['messages'] ?? null, $response->getReasonPhrase()),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $client = new HttpClient([
            'auth' => [$this->organizationId, $this->apiKey],
        ]);

        $url = $this->getResourceOwnerDetailsUrl($token);
        $response = $client->request('GET', $url);

        return $this->parseResponse($response);
    }
}
