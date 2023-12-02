<?php namespace verbb\auth\clients\mollie\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class MollieResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Set response
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->response['id'];
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

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['email'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getRegistrationNumber(): ?string
    {
        return $this->response['registrationNumber'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getVatNumber(): ?string
    {
        return $this->response['vatNumber'] ?? null;
    }
}
