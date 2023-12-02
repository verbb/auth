<?php

namespace verbb\auth\clients\strava\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class StravaResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response.
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Creates new resource owner.
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->response['id'] ?: null;
    }

    /**
     * Returns resource owner first name.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->response['firstname'] ?: null;
    }

    /**
     * Returns resource owner last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->response['lastname'] ?: null;
    }

    /**
     * Returns resource owner premium membership status.
     *
     * @return bool
     */
    public function getPremium(): bool
    {
        return $this->response['premium'];
    }

    /**
     * Returns all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
