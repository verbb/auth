<?php

namespace verbb\auth\clients\azure\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class AzureResourceOwner implements ResourceOwnerInterface
{
    /**
     * Response payload
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Creates new azure resource owner.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieves id of resource owner.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->claim('oid');
    }

    /**
     * Retrieves first name of resource owner.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->claim('given_name');
    }

    /**
     * Retrieves last name of resource owner.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->claim('family_name');
    }

    /**
     * Retrieves user principal name of resource owner.
     *
     * @return string|null
     */
    public function getUpn(): ?string
    {
        return $this->claim('upn');
    }

    /**
     * Retrieves user principal name of resource owner.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->claim('upn');
    }

    /**
     * Retrieves tenant id of resource owner.
     *
     * @return string|null
     */
    public function getTenantId(): ?string
    {
        return $this->claim('tid');
    }

    /**
     * Returns a field from the parsed JWT data.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function claim(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Returns all the data obtained about the user.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
