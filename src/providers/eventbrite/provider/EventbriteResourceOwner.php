<?php namespace verbb\auth\providers\eventbrite\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class EventbriteResourceOwner implements ResourceOwnerInterface
{
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
     * Get user id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['id'] ?? null;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['emails'][0]['email'] ?? null;
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
