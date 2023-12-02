<?php

namespace verbb\auth\clients\yahoo\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class YahooUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected array $response = [];


    /**
     * @var image URL
     */
    private image $imageUrl;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['profile']['guid'];
    }

    /**
     * Get perferred display name.
     *
     * @return string
     */
    public function getName(): string
    {
        /*
        nickname is not coming in the response.
        $this->response['profile']['nickname']
        */
        return $this->getFirstName() . " " . $this->getLastName();
    }

    /**
     * Get perferred first name.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->response['profile']['givenName'];
    }

    /**
     * Get perferred last name.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->response['profile']['familyName'];
    }

    /**
     * Get email address.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        if (!empty($this->response['profile']['emails'])) {
            return $this->response['profile']['emails'][0]['handle'];
        }

        return null;
    }

    /**
     * Get avatar image URL.
     *
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->response['imageUrl'];
    }

    public function setImageURL($url): YahooUser
    {
        $this->response['imageUrl'] = $url;
        return $this;
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
