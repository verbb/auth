<?php
namespace verbb\auth\clients\onecrm\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class OneCrmResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected array $response = [];

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }

    public function getName(): string
    {
        return $this->getValueByKey($this->response, 'name');
    }

    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'email');
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
