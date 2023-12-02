<?php

namespace verbb\auth\clients\zoho\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class ZohoUser implements ResourceOwnerInterface
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
        return $this->response['ZUID'];
    }

    public function getZUID()
    {
        return $this->response['ZUID'];
    }

    /**
     * Get preferred display name.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->response['Display_Name'];
    }

    /**
     * Get preferred first name.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->getResponseValue('First_Name');
    }

    /**
     * Get preferred last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->getResponseValue('Last_Name');
    }

    /**
     * Get email address.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getResponseValue('Email');
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

    private function getResponseValue($key)
    {
        return $this->response[$key] ?? null;
    }
}
