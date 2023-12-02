<?php

namespace verbb\auth\clients\mixer\entity;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Class MixerUser
 * @package Morgann\OAuth2\Client\Mixer\Entity
 */
class MixerUser  implements ResourceOwnerInterface
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

    /**
     * Get id
     * @return int
     */
    public function getId(): int
    {
        return $this->response['id'];
    }

    /**
     * Get avatar
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->response['avatarUrl'];
    }

    /**
     * Get bio
     * @return string
     */
    public function getBio(): string
    {
        return $this->response['bio'];
    }

    /**
     * Get createdAt
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->response['createdAt'];
    }

    /**
     * Get email
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['email'];
    }

    /**
     * Get experience
     * @return int
     */
    public function getExperience(): int
    {
        return $this->response['experience'];
    }

    /**
     * Get level
     * @return int
     */
    public function getLevel(): int
    {
        return $this->response['level'];
    }

    /**
     * Get display name
     * @return string
     */
    public function getName(): string
    {
        return $this->response['username'];
    }

    /**
     * Get array
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }
}
