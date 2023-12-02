<?php

namespace verbb\auth\clients\okta\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class OktaUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected array $response = [];

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['sub'];
    }

    /**
     * Get preferred display name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->response['name'];
    }

    /**
     * Get preferred first name.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->response['given_name'];
    }

    /**
     * Get preferred last name.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->response['family_name'];
    }

    /**
     * Get locale.
     *
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->response['locale'] ?? null;
    }

    /**
     * Get email address.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['email'] ?? null;
    }
    
    /**
     * Get preferred username.
     *
     * @return string|null
     */
    public function getPreferredUsername(): ?string
    {
        return $this->response['preferred_username'] ?? null;
    }
    
    /**
     * Get timezone for user.
     *
     * @return string|null
     */
    public function getZoneInfo(): ?string
    {
        return $this->response['zoneinfo'] ?? null;
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
