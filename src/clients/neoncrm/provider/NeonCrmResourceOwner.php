<?php
namespace verbb\auth\clients\neoncrm\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class NeonCrmResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected array $response = [];

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'individualAccount.accountId');
    }

    public function getName(): string
    {
        return $this->getValueByKey($this->response, 'individualAccount.primaryContact.firstName') . ' ' . $this->getValueByKey($this->response, 'individualAccount.primaryContact.lastName');
    }

    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'individualAccount.primaryContact.email1');
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
