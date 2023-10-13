<?php

namespace verbb\auth\clients\tumblr\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class TumblrUser implements ResourceOwnerInterface
{
    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['user']['id'];
    }

    /**
     * Get perferred first name.
     *
     * @return string
     */
    public function getNickname(): string
    {
        return $this->response['user']['name'];
    }

    /**
     * Get email address.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['user']['email'];
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
