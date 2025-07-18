<?php
namespace verbb\auth\clients\marketo\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class MarketoResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    protected array $response;

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function getId(): ?string
    {
        return null; // No ID in token
    }

    public function getName(): ?string
    {
        return null;
    }

    public function toArray(): array
    {
        return $this->response;
    }
}