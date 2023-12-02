<?php

namespace verbb\auth\clients\disqus\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class DisqusResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Token
     *
     * @var AccessToken
     */
    protected AccessToken $token;

    /**
     * Creates new resource owner.
     *
     */
    public function __construct(array $response, AccessToken $token)
    {
        $this->response = $response;
        $this->token = $token;
    }

    /**
     * Get resource owner id.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->response['id'] ?? null;
    }

    /**
     * Get resource owner's display name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->response['name'] ?? null;
    }

    /**
     * Get resource owner's email.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['email'] ?? null;
    }

    /**
     * Get resource owner's username
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->response['username'] ?? null;
    }

    /**
     * Get resource owner's image url
     *
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->response['avatar']['permalink'] ?? null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
