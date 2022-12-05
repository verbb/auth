<?php

namespace verbb\auth\clients\odnoklassniki\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class OdnoklassnikiResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    private $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->response['uid'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->response['name'];
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->response['first_name'];
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->response['last_name'];
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->response['pic_3'];
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->response['gender'];
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->response['location']['city'];
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->response['locale'];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->response;
    }
}
