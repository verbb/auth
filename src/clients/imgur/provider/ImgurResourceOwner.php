<?php

namespace verbb\auth\clients\imgur\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class ImgurResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->response['data']['id'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response['data'];
    }

    /**
     * Get resource owner url
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->response['data']['url'] ?: null;
    }

    /**
     * Get Imgur bio
     *
     * @return mixed
     */
    public function getBio(): mixed
    {
        return $this->response['data']['bio'] ?: null;
    }

    /**
     * Get resource owner reputation
     *
     * @return mixed
     */
    public function getReputation(): mixed
    {
        return $this->response['data']['reputation'] ?: null;
    }

    /**
     * Get created at timestamp
     *
     * @return string|null
     */
    public function getCreated(): ?string
    {
        return $this->response['data']['created'] ?: null;
    }

    /**
     * Get pro account expiration timestamp
     *
     * @return string|null
     */
    public function getProExpiration(): ?string
    {
        return $this->response['data']['pro_expiration'] ?: null;
    }
}
