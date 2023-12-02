<?php
namespace verbb\auth\clients\constantcontact\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class ConstantContactAccount implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;
    
    protected array $response = [];

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'encoded_account_id');
    }

    public function getName(): string
    {
        return $this->getValueByKey($this->response, 'organization_name');
    }

    public function toArray(): array
    {
        return $this->response;
    }

}
