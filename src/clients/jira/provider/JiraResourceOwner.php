<?php

namespace verbb\auth\clients\jira\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class JiraResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

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
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }
    
    /**
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getValueByKey($this->response, 'emailAddress');
    }
    
    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getValueByKey($this->response, 'accountId');
    }
    
    /**
     * Get resource owner name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getValueByKey($this->response, 'displayName');
    }
    
    /**
     * Get resource owner nickname
     *
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->getValueByKey($this->response, 'name');
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
