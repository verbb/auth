<?php

namespace verbb\auth\clients\buffer\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class BufferUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    private array $response;

    /**
     * @var array
     */
    private array $defaultFields = array(
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
    public function getId(): ?string
    {
        return $this->response['id'];
    }

    /**
     * Get the name for the user
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->response['name'];
    }

    /**
     * Return all the data for the user
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
