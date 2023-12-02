<?php
namespace verbb\auth\clients\freeagent\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class FreeAgentResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected array $response = [];

    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    public function getId()
    {
        // Return `https://api.freeagent.com/v2/users/12345`
        $url = $this->getValueByKey($this->response, 'user.url');
        $parts = explode('/', $url);

        return array_pop($parts);
    }

    public function getName(): string
    {
        return implode(' ', [$this->getFirstName(), $this->getLastName()]);
    }

    public function getFirstName()
    {
        return $this->getValueByKey($this->response, 'user.first_name');
    }

    public function getLastName()
    {
        return $this->getValueByKey($this->response, 'user.last_name');
    }

    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'user.email');
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
