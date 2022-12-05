<?php namespace verbb\auth\providers\pinterest\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class PinterestResourceOwner implements ResourceOwnerInterface
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
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['data']['id'] ?: null;
    }

    /**
     * Get user imageurl
     *
     * @return string|null
     */
    public function getImageurl()
    {
        if (empty($this->response['data']['image']['60x60']['url'])) {
            return null;
        }
        return $this->response['data']['image']['60x60']['url'];
    }
    /**
     * Alias for getImageurl() for higher compatablility.
     *
     * @return string|null
     */
    public function getAvatar()
    {
        return $this->getImageurl();
    }
    /**
     * Alias for getImageurl() for higher compatablility.
     *
     * @return string|null
     */
    public function getPictureUrl()
    {
        return $this->getImageurl();
    }

    /**
     * Get resource first name.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->response['data']['first_name'] ?: null;
    }
    /**
     * Get resource last name.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->response['data']['last_name'] ?: null;
    }

    /**
     * Get user nickname
     *
     * @return string|null
     */
    public function getNickname()
    {
        return $this->response['data']['username'] ?: null;
    }
    /**
     * Alias for getNickname() for higher compatablility.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->getNickname();
    }

    /**
     * Get resource url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->response['data']['url'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response['data'];
    }
}
