<?php
namespace verbb\auth\clients\telegram\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class TelegramUser implements ResourceOwnerInterface
{
    protected array $response = [];

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['id'];
    }

    public function getName()
    {
        return $this->response['username'];
    }
    
    public function getEmail(): mixed
    {
        return null;
    }

    public function toArray(): array
    {
        return $this->response;
    }

    private function getResponseValue($key)
    {
        return $this->response[$key] ?? null;
    }
}