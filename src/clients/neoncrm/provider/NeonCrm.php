<?php
namespace verbb\auth\clients\neoncrm\provider;

use GuzzleHttp\Client as HttpClient;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

class NeonCrm extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected $organizationId;
    protected $apiKey;

    public function getBaseAuthorizationUrl()
    {
        return $this->baseUrl() . '/np/oauth/auth';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->baseUrl() . '/np/oauth/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.neoncrm.com/v2/accounts/' . $token->getToken();
    }

    protected function baseUrl()
    {
        return 'https://' . $this->organizationId . '.app.neoncrm.com';
    }

    protected function getDefaultScopes()
    {
        return ['openid'];
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new NeonCrmResourceOwner($response);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                (isset($data['error']['message']) ? $data['error']['message'] : $response->getReasonPhrase()),
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
