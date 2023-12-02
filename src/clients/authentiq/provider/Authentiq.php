<?php
/**
 * Created by alexkeramidas for Authentiq B.V.
 * Authentiq
 * User: alexkeramidas
 * Date: 14/3/2017
 * Time: 8:28 μμ
 */

namespace verbb\auth\clients\authentiq\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Grant\AbstractGrant;
use verbb\auth\clients\authentiq\token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class Authentiq extends AbstractProvider
{
    protected array $scope = [];
    protected $idToken, $urlAuthorize, $urlAccessToken, $algorithm;
    protected string $domain = '';

    protected function domain(): string
    {
        return $this->domain ?? 'https://connect.authentiq.io/';
    }

    public function getBaseAuthorizationUrl(): string
    {
        return $this->urlAuthorize ?? $this->domain() . '/authorize';
    }


    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->urlAccessToken ?? $this->domain() . '/token';
    }


    /**
     * The function needs to be implemented because its an abstract one. It  would built the /userinfo but it is not used anywhere so we return null from it
     * Todo: We may use this function as a fallback to the idtoken. So here we should return the normal endpoint
     * @param \League\OAuth2\Client\Token\AccessToken $token
     * @return null
     */

    public function getResourceOwnerDetailsUrl(\League\OAuth2\Client\Token\AccessToken $token): null
    {
        return null;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getProviderAlgorithm(): ?array
    {
        if (isset($this->algorithm)) {
            return [$this->algorithm];
        }

        return ['HS256'];
    }


    public function getDomain(): string
    {
        return $this->domain();
    }

    /**
     * Read the set scopes and add openid to them if it does not exist
     * @return array|string
     */

    protected function getDefaultScopes(): array|string
    {
        if (in_array("openid", explode(" ", $this->scope))) {
            return $this->scope;
        }

        $scopes = explode(" ", $this->scope);
        $scopes[] = "openid";
        return implode(" ", $scopes);
    }


    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @return void
     * This is the first function that is called when returning from the OP.
     * Check for the http response but let it continue the execution to remain on the base package flow since check response is a void function.
     * Error handling is done by the base package
     */

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (!empty($data['error'])) {
            $code  = 0;
            $error = $data['error'];
            if (is_array($error)) {
                $code  = $error['code'];
                $error = $error['message'];
            }
            throw new IdentityProviderException($error, $code, $data);
        }
    }


    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Override the original getResourceOwner function with its original AccessToken Argument which actually is an Authentiq AccessToken.
     * Normally here we would make a call to fetch the user data from user_info but instead we get the IDtoken from $token and get it's claims to create the user info ourselves with createResourceOwner
     *
     * Check here for the original: https://github.com/thephpleague/oauth2-client/blob/master/src/Provider/AbstractProvider.php#L742
     *
     * @param \League\OAuth2\Client\Token\AccessToken $token
     * @return AuthentiqResourceOwner
     */


    public function getResourceOwner(\League\OAuth2\Client\Token\AccessToken $token): AuthentiqResourceOwner
    {
        $data = $token->getIdTokenClaims();
        return $this->createResourceOwner($data, $token);
    }

    /**
     * Implement the abstract function and we return an Authentiq Resource owner
     * Use \League\OAuth2\Client\Token\AccessToken in the argument so that we can implement the function based on its declaration
     * @param array $response
     * @param \League\OAuth2\Client\Token\AccessToken $token
     * @return AuthentiqResourceOwner
     */

    protected function createResourceOwner(array $response, \League\OAuth2\Client\Token\AccessToken $token): AuthentiqResourceOwner
    {
        return new AuthentiqResourceOwner($response);
    }


    /**
     * Create the Authentiq access token instead of the original one
     *
     *  We name the response of this function  Access token (of Authentiq\Oauth2\Client\Token\AccessToken) to override the function declaration
     *
     */
    protected function createAccessToken(array $response, AbstractGrant $grant): AccessToken
    {
        return new AccessToken($response, $this, $this->clientSecret);
    }
}