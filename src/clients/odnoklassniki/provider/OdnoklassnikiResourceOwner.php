<?php

namespace verbb\auth\clients\odnoklassniki\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class OdnoklassnikiResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    private array $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['uid'];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->response['name'];
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->response['first_name'];
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->response['last_name'];
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->response['pic_3'];
    }

    /**
     * @return string
     */
    public function getGender(): string
    {
        return $this->response['gender'];
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->response['location']['city'];
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->response['locale'];
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
