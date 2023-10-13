<?php

namespace verbb\auth\clients\linkedin\token;

use League\OAuth2\Client\Token\AccessToken;

class LinkedInAccessToken extends AccessToken
{
    /**
     * @var int
     */
    protected mixed $refreshTokenExpires;

    /**
     * Constructs an access token.
     *
     * @param array $options An array of options returned by the service provider
     *     in the access token request. The `access_token` option is required.
     * @throws InvalidArgumentException if `access_token` is not provided in `$options`.
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (isset($options['refresh_token_expires_in'])) {
            $expires = $options['refresh_token_expires_in'];
            if (!$this->isExpirationTimestamp($expires)) {
                $expires += time();
            }
            $this->refreshTokenExpires = $expires;
        }
    }

    /**
     * Returns the refresh token expiration timestamp, if defined.
     *
     * @return integer|null
     */
    public function getRefreshTokenExpires(): ?int
    {
        return $this->refreshTokenExpires;
    }
}
