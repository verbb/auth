<?php namespace verbb\auth\clients\microsoft\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class MicrosoftResourceOwner implements ResourceOwnerInterface
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
     * Get user id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->response['id'] ?: null;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['emails']['preferred'] ?: null;
    }

    /**
     * Get user firstname
     *
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->response['first_name'] ?: null;
    }

    /**
     * Get user lastname
     *
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->response['last_name'] ?: null;
    }

    /**
     * Get user name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->response['name'] ?: null;
    }

    /**
     * Get user urls
     *
     * @return string|null
     */
    public function getUrls(): ?string
    {
        return isset($this->response['link']) ? $this->response['link'].'/cid-'.$this->getId() : null;
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
