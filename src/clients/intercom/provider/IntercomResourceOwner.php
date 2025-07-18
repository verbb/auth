<?php
namespace verbb\auth\clients\intercom\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class IntercomResourceOwner implements ResourceOwnerInterface
{
    protected array $response;

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function getId(): ?string
    {
        return $this->getValueByKey($this->response, 'id');
    }

    public function getName(): ?string
    {
        return $this->getValueByKey($this->response, 'name');
    }

    public function getEmail(): ?string
    {
        return $this->getValueByKey($this->response, 'email');
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
