<?php namespace verbb\auth\clients\instagram\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class InstagramResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
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
        return $this->response['id'] ?: null;
    }

    /**
     * Get user nickname
     *
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->response['username'] ?: null;
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
