<?php

namespace verbb\auth\clients\dropbox\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class DropboxResourceOwner implements ResourceOwnerInterface
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
     * @return string
     */
    public function getId(): string
    {
        return $this->getValueByKey($this->response, 'account_id');
    }

    /**
     * Get resource owner name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getValueByKey($this->response, 'name.display_name');
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
