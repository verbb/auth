<?php
namespace verbb\auth\clients\freeagent\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class FreeAgentResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected $response;

    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }

    public function getName()
    {
        return $this->getValueByKey($this->response, 'name');
    }

    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'email');
    }

    public function toArray()
    {
        return $this->response;
    }
}
