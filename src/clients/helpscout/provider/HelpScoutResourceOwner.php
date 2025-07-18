<?php
namespace verbb\auth\clients\helpscout\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class HelpScoutResourceOwner implements ResourceOwnerInterface
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

    public function getName(): ?string
    {
        return $this->getValueByKey($this->response, 'firstName') . ' ' . $this->getValueByKey($this->response, 'lastName');
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