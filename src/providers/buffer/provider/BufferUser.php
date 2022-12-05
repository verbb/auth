<?php

namespace verbb\auth\providers\buffer\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class BufferUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    private $response;

    /**
     * @var array
     */
    private $defaultFields = array(
        'id' => null,
        'name' => null,
    );

    /**
     * @param array $response
     */
    public function __construct(array $response = array())
    {
        $this->response = array_merge($this->defaultFields, $response);
    }

    /**
     * Get the ID for the user
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['id'];
    }

    /**
     * Get the name for the user
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['name'];
    }

    /**
     * Return all the data for the user
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
