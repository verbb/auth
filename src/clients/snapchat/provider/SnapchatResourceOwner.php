<?php namespace verbb\auth\clients\snapchat\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class SnapchatResourceOwner implements ResourceOwnerInterface
{
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
        return $this->response['data']['id'] ?: null;
    }

    /**
     * Get user imageurl
     *
     * @return string|null
     */
    public function getImageurl(): ?string
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
    public function getAvatar(): ?string
    {
        return $this->getImageurl();
    }

    /**
     * Alias for getImageurl() for higher compatablility.
     *
     * @return string|null
     */
    public function getPictureUrl(): ?string
    {
        return $this->getImageurl();
    }

    /**
     * Get resource first name.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->response['data']['first_name'] ?: null;
    }

    /**
     * Get resource last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->response['data']['last_name'] ?: null;
    }

    /**
     * Get user nickname
     *
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->response['data']['username'] ?: null;
    }

    /**
     * Alias for getNickname() for higher compatablility.
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->getNickname();
    }

    /**
     * Get resource url.
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->response['data']['url'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response['data'];
    }
}