<?php

namespace verbb\auth\clients\steemconnect\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\steemconnect\common\http\Request;
use verbb\auth\clients\steemconnect\config\Config;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Class Provider.
 *
 * SteemConnect v2 OAuth2 client.
 *
 * This class implements League's OAuth client for SteemConnect authentication on PHP projects.
 */
class Provider extends AbstractProvider
{
    /*
     * Traits: Bearer Authorization.
     */
    use BearerAuthorizationTrait;

    /**
     * @var Config Instance of the configuration class holder.
     */
    protected Config $config;

    /**
     * @TODO check error messages for correct data key.
     *
     * @var string Erro key to parse error responses.
     */
    protected string $responseError = 'error';

    /**
     * @var string Current response code.
     */
    protected string $responseCode = '';

    public function getDefaultScopes() : array
    {
        return $this->config->getScopes();
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->config->buildUrl('account');
    }

    /**
     * Provider constructor.
     *
     * @param Config $config Provider configuration instance.
     */
    public function __construct(Config $config)
    {
        // assign config on class scope.
        $this->config = $config;

        // call parent constructor to init custom logic.
        parent::__construct($this->parseProviderOptions(), []);
    }

    /**
     * Parses the config object into required provider options.
     *
     * @return array
     */
    protected function parseProviderOptions(): array
    {
        return [
            'redirectUri'  => $this->config->getReturnUrl(),
            'clientId'     => $this->config->getClientId(),
            'clientSecret' => $this->config->getClientSecret(),
        ];
    }

    /**
     * @var string Key used in a token response to identify the resource owner.
     */
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'username';

    public function getBaseAuthorizationUrl(): string
    {
        return $this->config->buildUrl('authorization');
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->config->buildUrl('access_token');
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (!empty($data[$this->responseError])) {
            $error = array_get($data, $this->responseError, null);

            $code = $this->responseCode && !empty(array_get($data, $this->responseCode)) ? array_get($data, $this->responseCode) : 0;

            throw new IdentityProviderException($error, $code, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwner|ResourceOwnerInterface
    {
        return new ResourceOwner($response);
    }

    /**
     * Parses a return from the authorization flow and returns a access token instance when possible.
     *
     * @param string|null $code
     *
     * @return AccessToken|null
     */
    public function parseReturn(string $code = null): ?AccessToken
    {
        // if no code was passed, request code will be detected, if any.
        $code = $code ?: Request::current()->query->get('code', null);

        // just return null for now.
        if (!$code) {
            return null;
        }

        // try a token exchange.

        // returns the token instance, if possible.
        return $this->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    /**
     * Issue a new Access Token from a refresh token.
     *
     * Notice:
     * Not all tokens are refreshable.
     * The access token may be issued without a refresh token,
     * OR, the access token instance was incorrectly stored,
     * meaning the refresh token is not present
     *
     * @param AccessToken $currentToken
     *
     * @return AccessToken|null
     */
    public function refreshToken(AccessToken $currentToken): ?AccessToken
    {
        // get the refresh token string from the current token.
        $refreshToken = $currentToken->getRefreshToken();

        // if the refresh token is not present...
        if (!$refreshToken) {
            // return null instead of trying the refresh flow.
            return null;
        }

        // call the refresh from string method.
        return $this->refreshTokenString($refreshToken);
    }

    /**
     * Issue a new Access Token from a refresh token.
     *
     * This method takes a token string as parameter
     * instead of an AccessToken instance.
     *
     * @param string $refreshToken
     *
     * @return AccessToken|null
     */
    public function refreshTokenString(string $refreshToken): ?AccessToken
    {
        // ask for a new access token using the refresh_token grant type.
        // returns the token instance, if possible.
        return $this->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * Encode a given AccessToken instance as JSON.
     *
     * @param AccessToken $token
     *
     * @return null|string
     */
    public function encodeToken(AccessToken $token) : ?string
    {
        return json_encode($token);
    }

    /**
     * Decode a given token into JSON.
     *
     * @param string $tokenJson
     *
     * @return AccessToken|null
     */
    public function decodeToken(string $tokenJson) : ?AccessToken
    {
        // decode the json into an array.
        $tokenData = json_decode($tokenJson, true);

        // returns null if the token does not have an access token key.
        if (!array_key_exists('access_token', $tokenData)) {
            return null;
        }

        // returns null if the given access_token key exists but the value is invalid.
        if (!$tokenData['access_token']) {
            return null;
        }

        // factory and return a new AccessToken instance.
        return new AccessToken($tokenData);
    }
}
