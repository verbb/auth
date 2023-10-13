<?php


namespace verbb\auth\clients\wechat\provider;

use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Tool\RequiredParameterTrait;
use League\OAuth2\Client\Token\AccessToken;
use verbb\auth\clients\wechat\grant\miniprogram\AuthorizationCode;
use Psr\Http\Message\ResponseInterface;
use InvalidArgumentException;
use LogicException;

class MiniProgramProvider extends AbstractProvider
{
    use ArrayAccessorTrait;
    use RequiredParameterTrait;

    protected $appid;
    protected $secret;
    protected $jscode;
    protected $responseUserInfo;

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *     Individual providers may introduce more options, as needed.
     * @param array $collaborators An array of collaborators that may be used to
     *     override this provider's default behavior. Collaborators include
     *     `grantFactory`, `requestFactory`, and `httpClient`.
     *     Individual providers may introduce more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $this->checkRequiredParameters(
            [
                'appid',
                'secret',
            ],
            $options
        );

        $options['access_token'] = 'js_code';

        parent::__construct($options, $collaborators);
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        throw new LogicException('use wx.login(OBJECT) to get js_code');
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://api.weixin.qq.com/sns/jscode2session';
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param  string $jsCode
     * @param  array $options
     * @return \Oakhope\OAuth2\Client\Token\MiniProgram\AccessToken
     */
    public function getAccessToken($jsCode, array $options = []): \Oakhope\OAuth2\Client\Token\MiniProgram\AccessToken
    {
        $this->jscode = $jsCode;
        $grant = new AuthorizationCode();
        $grant = $this->verifyGrant($grant);
        $params = [
            'appid' => $this->appid,
            'secret' => $this->secret,
            'js_code' => $jsCode,
        ];

        $params = $grant->prepareRequestParameters($params, $options);
        $request = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);
        $prepared = $this->prepareAccessTokenResponse($response);

        return $this->createAccessToken($prepared, $grant);
    }

    /**
     * Creates an access token from a response.
     *
     * The grant that was used to fetch the response can be used to provide
     * additional context.
     *
     * @param  array $response
     * @param  AbstractGrant $grant
     * @return \Oakhope\OAuth2\Client\Token\MiniProgram\AccessToken
     */
    protected function createAccessToken(array $response, AbstractGrant $grant): \Oakhope\OAuth2\Client\Token\MiniProgram\AccessToken
    {
        return new \Oakhope\OAuth2\Client\Token\MiniProgram\AccessToken(
            $response
        );
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        throw new LogicException(
            'use wx.getUserInfo(OBJECT) to get ResourceOwnerDetails'
        );
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $errcode = $this->getValueByKey($data, 'errcode');
        $errmsg = $this->getValueByKey($data, 'errmsg');

        if ($errcode || $errmsg) {
            throw new IdentityProviderException($errmsg, $errcode, $response);
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return ResourceOwnerInterface|MiniProgramResourceOwner
     */
    public function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface|MiniProgramResourceOwner
    {
        return new MiniProgramResourceOwner($response, $token, $this->appid);
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param AccessToken $token
     * @return ResourceOwnerInterface|MiniProgramResourceOwner
     */
    public function getResourceOwner(AccessToken $token): ResourceOwnerInterface|MiniProgramResourceOwner
    {
        if (null == $this->responseUserInfo) {
            throw new InvalidArgumentException(
                "setResponseUserInfo by wx.getUserInfo(OBJECT)'s response data first"
            );
        }

        return $this->createResourceOwner($this->responseUserInfo, $token);
    }

    /**
     * set by wx.getUserInfo(OBJECT)'s response data
     *
     * @param array $response
     */
    public function setResponseUserInfo(array $response): void
    {
        $this->responseUserInfo = $response;
    }
}
