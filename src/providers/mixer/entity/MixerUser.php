<?php

namespace verbb\auth\providers\mixer\entity;

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
    protected $response;

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
    public function getId()
    {
        return $this->response['id'];
    }

    /**
     * Get avatar
     * @return string
     */
    public function getAvatar()
    {
        return $this->response['avatarUrl'];
    }

    /**
     * Get bio
     * @return string
     */
    public function getBio()
    {
        return $this->response['bio'];
    }

    /**
     * Get createdAt
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->response['createdAt'];
    }

    /**
     * Get email
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'];
    }

    /**
     * Get experience
     * @return int
     */
    public function getExperience()
    {
        return $this->response['experience'];
    }

    /**
     * Get level
     * @return int
     */
    public function getLevel()
    {
        return $this->response['level'];
    }

    /**
     * Get display name
     * @return string
     */
    public function getName()
    {
        return $this->response['username'];
    }

    /**
     * Get array
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
