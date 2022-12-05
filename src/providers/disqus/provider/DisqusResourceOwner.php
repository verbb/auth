<?php

namespace verbb\auth\providers\disqus\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class DisqusResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Token
     *
     * @var \League\OAuth2\Client\Token\AccessToken
     */
    protected $token;

    /**
     * Creates new resource owner.
     *
     * @param array $response
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
    public function getId()
    {
        return $this->response['id'] ?? null;
    }

    /**
     * Get resource owner's display name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['name'] ?? null;
    }

    /**
     * Get resource owner's email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'] ?? null;
    }

    /**
     * Get resource owner's username
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->response['username'] ?? null;
    }

    /**
     * Get resource owner's image url
     *
     * @return string|null
     */
    public function getAvatar()
    {
        return $this->response['avatar']['permalink'] ?? null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
