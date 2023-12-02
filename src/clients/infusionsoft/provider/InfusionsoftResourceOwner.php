<?php

namespace verbb\auth\clients\infusionsoft\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class InfusionsoftResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
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

    public function getId(): int
    {
        return $this->response['global_user_id'];
    }

    public function getEmail(): string
    {
        return $this->response['email'];
    }

    public function getFamilyName(): string
    {
        return $this->response['family_name'];
    }

    public function getGivenName(): string
    {
        return $this->response['given_name'];
    }

    public function getInfusionsoftId(): string
    {
        return $this->response['infusionsoft_id'];
    }

    public function getSub(): string
    {
        return $this->response['sub'];
    }

    public function getMiddleName(): ?string
    {
        return $this->response['middle_name'];
    }

    public function getPreferredName(): ?string
    {
        return $this->response['preferred_name'];
    }

    public function toArray(): array
    {
        return $this->response;
    }
}