<?php
namespace verbb\auth\clients\amazoncognito\provider;

use verbb\auth\helpers\ProviderError;

use League\OAuth2\Client\Exception\HostedDomainException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class AmazonCognito extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var array List of scopes that will be used for authentication.
     *
     * Valid scopes: phone, email, openid, aws.cognito.signin.user.admin, profile
     * Defaults to email, openid
     *
     */
    protected $scopes = [];

    /**
     * @var string The full domain to AWS Cognito.
     */
    protected $domain;

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (!empty($options['domain'])) {
            $this->domain = $options['domain'];
        } else {
            throw new \InvalidArgumentException(
                'The "domain" option must be set.'
            );
        }

        if (!empty($options['scope'])) {
            if (is_array($options['scope'])) {
                $this->scopes = $options['scope'];
            } else {
                $this->scopes = explode($this->getScopeSeparator(), $options['scope']);
            }
        }
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Returns the url for given action
     *
     * @param $action
     * @return string
     */
    private function getCognitoUrl($action)
    {
        return rtrim($this->domain, '/') . '/' . ltrim($action, '/');
    }

    /**
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->getCognitoUrl('oauth2/authorize');
    }

    /**
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getCognitoUrl('oauth2/token');
    }

    /**
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getCognitoUrl('oauth2/userInfo');
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getAuthorizationParameters(array $options)
    {
        $scopes = array_merge($this->getDefaultScopes(), $this->scopes);

        if (!empty($options['scope'])) {
            $scopes = array_merge($scopes, $options['scope']);
        }

        $options['scope'] = array_unique($scopes);

        return parent::getAuthorizationParameters($options);
    }

    /**
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['openid', 'email', 'profile'];
    }

    /**
     * @return string
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * @param ResponseInterface $response
     * @param array|string $data
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (empty($data['error'])) {
            return;
        }

        $code = 0;
        $error = ProviderError::message($data, $response->getReasonPhrase());

        throw new IdentityProviderException($error, $code, $data);
    }

    /**
     * @param array $response
     * @param AccessToken $token
     * @return AmazonCognitoUser|\League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new AmazonCognitoUser($response);

        return $user;
    }
}
