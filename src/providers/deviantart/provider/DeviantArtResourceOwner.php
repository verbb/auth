<?php

namespace verbb\auth\providers\deviantart\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class DeviantArtResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the uuid of the authorized resource owner
     *
     * @return string
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'userid');
    }

    /**
     * Returns the username
     *
     * @return string
     */
    public function getName()
    {
        return $this->getValueByKey($this->response, 'username');
    }

    /**
     * Returns the URL for the user's icon
     * Always ends with "?n" where n is a cache-busting number incremented after each avatar change
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->getValueByKey($this->response, 'usericon');
    }

    /**
     * Returns the user's "type"
     * Known values are:
     *  - banned (used for deactivated accounts too)
     *  - regular
     *  - premium, hell-premium
     *  - beta, hell-beta
     *  - senior
     *  - volunteer
     *  - admin
     *
     * @return string
     */
    public function getType()
    {
        return $this->getValueByKey($this->response, 'type');
    }

    public function toArray()
    {
        return $this->response;
    }
}
