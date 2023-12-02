<?php namespace verbb\auth\clients\box\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class BoxResourceOwner implements ResourceOwnerInterface
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
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['login'] ?: null;
    }

    /**
     * Get resource owner name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->response['name'] ?: null;
    }

    /**
     * Get resource owner avatar url
     *
     * @return string|null
     */
    public function getAvatarUrl(): ?string
    {
        return $this->response['avatar_url'] ?: null;
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
