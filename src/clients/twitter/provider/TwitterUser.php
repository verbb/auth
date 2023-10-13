<?php

namespace verbb\auth\clients\twitter\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class TwitterUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected mixed $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response['data'] ?? [];
    }

    public function getId()
    {
        return $this->response['id'];
    }

    public function getName()
    {
        return $this->response['name'];
    }

    public function getUsername()
    {
        return $this->response['username'];
    }

    public function getEmail()
    {
        return $this->response['email'];
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

    private function getResponseValue($key)
    {
        return $this->response[$key] ?? null;
    }
}
