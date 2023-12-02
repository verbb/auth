<?php namespace verbb\auth\clients\eventbrite\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class EventbriteResourceOwner implements ResourceOwnerInterface
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
     * Get user id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->response['id'] ?? null;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['emails'][0]['email'] ?? null;
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
