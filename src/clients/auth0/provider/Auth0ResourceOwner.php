<?php
namespace verbb\auth\clients\auth0\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class Auth0ResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * @var array
     */
    protected array $response = [];

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'user_id') ?? $this->getValueByKey($this->response, 'sub');
    }

    /**
     * Returns email address of the resource owner
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * Returns full name of the resource owner
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getValueByKey($this->response, 'name');
    }

    /**
     * Returns nickname of the resource owner
     *
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->getValueByKey($this->response, 'nickname');
    }

    /**
     * Returns identities of the resource owner
     *
     * @see https://auth0.com/docs/user-profile/user-profile-structure
     * @return array|null
     */
    public function getIdentities(): ?array
    {
        return $this->getValueByKey($this->response, 'identities');
    }

    /**
     * Returns picture url of the resource owner
     *
     * @return string|null
     */
    public function getPictureUrl(): ?string
    {
        return $this->getValueByKey($this->response, 'picture');
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
