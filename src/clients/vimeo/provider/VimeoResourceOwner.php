<?php

namespace verbb\auth\clients\vimeo\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class VimeoResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Token
     *
     * @var AccessToken
     */
    protected AccessToken $token;

    /**
     * Creates new resource owner.
     *
     */
    public function __construct(array $response, AccessToken $token)
    {
        $this->response = $response;
        $this->token = $token;
    }

    /**
     * Get resource owner id.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        $uri = $this->response['uri'];

        return substr($uri, strrpos($uri, '/') + 1);
    }

    /**
     * Get resource owner's display name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->response['name'] ?: null;
    }

    /**
     * Get resource owner's username
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        $username = null;
        $link = $this->getLink();

        if (!empty($link)) {
            $username = substr($link, strrpos($link, '/') + 1);
        }

        return $username;
    }

    /**
     * Get resource owner's url link
     *
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->response['link'] ?: null;
    }

    /**
     * Get resource owner's image url
     *
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        $avatarUrl = null;
        $avatarWidth = 0;

        // find the biggest image
        if (!empty($this->response['pictures']['sizes']) && is_array($this->response['pictures']['sizes'])) {
            foreach ($this->response['pictures']['sizes'] as $picture) {
                if ($picture['width'] > $avatarWidth) {
                    $avatarUrl = $picture['link'];
                    $avatarWidth = $picture['width'];
                }
            }
        }

        return $avatarUrl;
    }

    /**
     * Get the token scope
     *
     * @return string|null
     */
    public function getTokenScope(): ?string
    {
        $values = $this->token->getValues();
        return empty($values['scope']) ? null : $values['scope'];
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
