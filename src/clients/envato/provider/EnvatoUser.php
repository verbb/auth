<?php
namespace verbb\auth\clients\envato\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class EnvatoUser implements ResourceOwnerInterface
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
     * @param array $response
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
        return $this->response['id'] ?: NULL;
    }

    /**
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['email'] ?: NULL;
    }


    /**
     * Get resource owner username
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->response['username'] ?: NULL;
    }

    /**
     * Get resource owner purchases array
     *
     * @return array
     */
    public function getPurchases(): array
    {
        return $this->response['results'] ?: [];
    }

    /**
     * Get resource owner purchases amount
     *
     * @return array
     */
    public function getPurchasesCount(): array
    {
        return $this->response['count'] ?: [];
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
