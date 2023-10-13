<?php

namespace verbb\auth\clients\myob\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use InvalidArgumentException;

class MYOB extends AbstractProvider
{
    use BearerAuthorizationTrait;

    private bool $cftokenSent = false;

    /*
     * options:
     *     username=xxx
     *     password=xxx
     *     companyName=xxx
     *     clientId=xxx
     *     clientSecret=xxx
     *     redirectUri=xxx
     */

    public function __construct(array $options = [], array $collaborators = [])
    {

        //$collaborators['optionProvider'] = new MYOBOptionProvider();

        if (!isset($options['username']) || !isset($options['password']) || !isset($options['companyName'])) {
            throw new InvalidArgumentException("Company Name, username or password not set");
        }


        $this->companyName = $options['companyName'];
        $this->cftoken = base64_encode("{$options['username']}:{$options['password']}");
        parent::__construct($options, $collaborators);
    }


    /**
     * Get base URL for authorizing a client
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://secure.myob.com/oauth2/account/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://secure.myob.com/oauth2/v1/authorize';
    }

    /**
     * Get URL for requesting the resource owner's details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://secure.myob.com/oauth2/v1/Validate?scope=CompanyFile';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return ['CompanyFile'];
    }

    /**
     * Check a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string $data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            $error = $data['ErrorCode'];
            $errorDescription = $data['Errors'][0]['Name'] . "\n" . $data['Errors'][0]['Message'];
            throw new IdentityProviderException(
                "MYOB API Error {$response->getBody()}: {$data['ErrorCode']}: {$data['Errors'][0]['Name']} ({$data['Errors'][0]['Message']})",
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful resource owner details request
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return MYOBResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): MYOBResourceOwner
    {
        return new MYOBResourceOwner($response);
    }

    /**
     * Returns the default headers used by this provider.
     *
     */
    protected function getDefaultHeaders($token = null): array
    {
        $headers = [
            'x-myobapi-version' => 'v2',
            'x-myobapi-key' => $this->clientId
        ];
        if (!($this->cftokenSent)) $headers['x-myobapi-cftoken'] = $this->cftoken;
        $this->cftokenSent = true;
        return $headers;
    }

    /**
     * Returns the authorization headers used by this provider.
     *
     * @param mixed|null $token Either a string or an access token instance
     * @return array
     */

    protected function getAuthorizationHeaders($token = null): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }


    public function getCompanyUrl($token)
    {
        sleep(3);
        $request = parent::getAuthenticatedRequest('GET', 'https://api.myob.com/accountright/', $token);
        $companies = $this->getParsedResponse($request);

        foreach ($companies as $company) {
            if ($company['Name'] == $this->companyName) return $company['Uri'];
        }
        throw new InvalidArgumentException("Could not retrieve Company URL for {$this->CompanyName}");
    }

}
