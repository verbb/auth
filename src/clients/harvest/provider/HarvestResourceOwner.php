<?php
namespace verbb\auth\clients\harvest\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class HarvestResourceOwner implements ResourceOwnerInterface
{
    /**
     * Domain
     *
     * @var string
     */
    protected string $domain = '';

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
        return $this->response['user']['id'] ?: null;
    }

    /**
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['user']['email'] ?: null;
    }

    /**
     * Get resource owner name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->response['user']['first_name'].' '.$this->response['user']['last_name'] ?: null;
    }

    /**
     * Get resource owner avatar url
     *
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->response['user']['avatar_url'] ?: null;
    }

    /**
     * Set resource owner domain
     *
     * @param string $domain
     *
     * @return ResourceOwner
     */
    public function setDomain(string $domain): ResourceOwner
    {
        $this->domain = $domain;

        return $this;
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
