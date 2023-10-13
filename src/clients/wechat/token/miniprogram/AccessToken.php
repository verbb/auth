<?php


namespace verbb\auth\clients\wechat\token\miniprogram;

use InvalidArgumentException;

class AccessToken extends \League\OAuth2\Client\Token\AccessToken
{
    /**
     * @var string
     */
    protected mixed $sessionKey;

    /**
     * @var string
     */
    protected mixed $openId;

    /**
     * @var string
     */
    protected mixed $unionId;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * Constructs an access token.
     *
     * @param array $options An array of options returned by the service provider
     *     in the access token request. The `access_token` option is required.
     * @throws InvalidArgumentException if `access_token` is not provided in `$options`.
     */
    public function __construct(array $options = [])
    {
        $options['access_token'] = 'session_key';

        parent::__construct($options);

        if (empty($options['session_key'])) {
            throw new InvalidArgumentException('Required option not passed: "session_key"');
        }

        $this->sessionKey = $options['session_key'];

        if (!empty($options['openid'])) {
            $this->openId = $options['openid'];
        }

        if (!empty($options['unionid'])) {
            $this->unionId = $options['unionid'];
        }

        // Capture any additional values that might exist in the token but are
        // not part of the standard response. Vendors will sometimes pass
        // additional user data this way.
        $this->values = array_diff_key($options, array_flip([
            'session_key',
            'openid',
            'unionid'
        ]));
    }

    /**
     * Returns the session key string of this instance.
     *
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * Returns the resource owner identifier, if defined.
     *
     * @return string|null
     */
    public function getOpenId(): ?string
    {
        return $this->openId;
    }

    /**
     * Returns the resource owner identifier, if defined.
     *
     * @return string|null
     */
    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    /**
     * Returns the token key.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getSessionKey();
    }

    /**
     * Returns an array of parameters to serialize when this is serialized with
     * json_encode().
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $parameters = $this->values;

        if ($this->sessionKey) {
            $parameters['sessionKey'] = $this->sessionKey;
        }

        if ($this->openId) {
            $parameters['openid'] = $this->openId;
        }

        if ($this->unionId) {
            $parameters['unionid'] = $this->unionId;
        }

        return $parameters;
    }
}
