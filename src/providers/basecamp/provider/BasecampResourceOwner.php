<?php

namespace verbb\auth\providers\basecamp\provider;

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
    protected $response;

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
    public function getId()
    {
        return $this->getValueByKey($this->response, 'identity.id');
    }

    /**
     * Get resource owner name
     *
     * @return string
     */
    public function getName()
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
    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'identity.email_address');
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
