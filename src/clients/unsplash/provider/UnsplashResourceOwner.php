<?php

namespace verbb\auth\clients\unsplash\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class UnsplashResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

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

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * @return mixed
     */
    public function getUsername(): mixed
    {
        return $this->getValueByKey($this->response, 'username');
    }

    /**
     * @return mixed
     */
    public function getName(): mixed
    {
        return $this->getValueByKey($this->response, 'name');
    }

    /**
     * @return mixed
     */
    public function getFirstName(): mixed
    {
        return $this->getValueByKey($this->response, 'first_name', '');
    }

    /**
     * @return mixed
     */
    public function getLastName(): mixed
    {
        return $this->getValueByKey($this->response, 'last_name', '');
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}