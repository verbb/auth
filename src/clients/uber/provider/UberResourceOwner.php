<?php namespace verbb\auth\clients\uber\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class UberResourceOwner implements ResourceOwnerInterface
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
     * Get user email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['email'] ?: null;
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
     * Get user imageurl
     *
     * @return string|null
     */
    public function getImageurl(): ?string
    {
        return $this->response['picture'] ?: null;
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
     * Get user userId
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->response['uuid'] ?: null;
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
