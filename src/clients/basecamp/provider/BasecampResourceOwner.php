<?php

namespace verbb\auth\clients\basecamp\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class BasecampResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getValueByKey($this->response, 'identity.id');
    }

    /**
     * Get resource owner name
     *
     * @return string
     */
    public function getName(): string
    {
        return implode(' ', array_filter([
            $this->getValueByKey($this->response, 'identity.first_name'),
            $this->getValueByKey($this->response, 'identity.last_name')
        ]));
    }

    /**
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getValueByKey($this->response, 'identity.email_address');
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
}
