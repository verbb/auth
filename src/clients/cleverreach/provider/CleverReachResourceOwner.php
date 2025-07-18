<?php
namespace verbb\auth\clients\cleverreach\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class CleverReachResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected array $response;

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function getId(): ?string
    {
        return $this->getValueByKey($this->response, 'id');
    }

    public function getEmail(): ?string
    {
        return $this->getValueByKey($this->response, 'email');
    }

    public function getName(): ?string
    {
        return $this->getValueByKey($this->response, 'name');
    }

    public function toArray(): array
    {
        return $this->response;
    }
}