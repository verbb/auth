<?php

namespace verbb\auth\clients\line\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class LineUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected array $response = [];

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['userId'];
    }

    /**
     * Get perferred display name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->response['displayName'];
    }

    public function getEmail(): bool
    {
        return false;
    }

    /**
     * Get avatar image URL.
     *
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        if (!empty($this->response['pictureUrl'])) {
            return $this->response['pictureUrl'];
        }

        return null;
    }

    /**
     * Get perferred statusMessage.
     *
     * @return string
     */
    public function getStatusMessage(): string
    {
        return $this->response['statusMessage'];
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
