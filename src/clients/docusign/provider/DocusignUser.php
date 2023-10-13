<?php

namespace verbb\auth\clients\docusign\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class DocusignUser implements ResourceOwnerInterface
{
    private array $userInfo;
    private AccessToken $token;

    public function __construct(
        array $userInfo,
        AccessToken $token
    ) {
        $this->userInfo = $userInfo;
        $this->token = $token;
    }

    public function getId()
    {
        return $this->userInfo['sub'];
    }

    public function toArray(): array
    {
        return $this->userInfo;
    }

    public function getName()
    {
        return $this->userInfo['name'];
    }

    public function getEmail()
    {
        return $this->userInfo['email'];
    }

    /**
     * Get default user account, if any exists.
     *
     * @return array|null
     */
    public function getDefaultAccount(): ?array
    {
        foreach ($this->userInfo['accounts'] as $account) {
            if ($account['is_default']) {
                return $account;
            }
        }

        return null;
    }

    /**
     * @return AccessToken
     */
    public function getToken(): AccessToken
    {
        return $this->token;
    }
}
