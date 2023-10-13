<?php

declare(strict_types=1);

namespace verbb\auth\clients\buddy\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

final class BuddyResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * @var array
     */
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getId()
    {
        return $this->getValueByKey($this->data, 'id');
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function getUrl(): string
    {
        return $this->getValueByKey($this->data, 'url');
    }

    public function getWorkspaceUrl(): string
    {
        return $this->getValueByKey($this->data, 'workspaces_url');
    }

    public function getAvatarUrl(): string
    {
        return $this->getValueByKey($this->data, 'avatar_url');
    }

    public function getName(): string
    {
        return $this->getValueByKey($this->data, 'name');
    }

    public function getTitle(): string
    {
        return $this->getValueByKey($this->data, 'title');
    }
}
