<?php


namespace verbb\auth\clients\rdio\provider;

use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class RdioResourceOwner implements ResourceOwnerInterface
{
    protected array $response = [];

    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['result']['key'];
    }

    public function getFirstName()
    {
        return $this->response['result']['firstName'];
    }

    public function getLastName()
    {
        return $this->response['result']['lastName'];
    }

    public function getGender()
    {
        return $this->response['result']['gender'];
    }

    public function getUrl()
    {
        return $this->response['result']['url'];
    }

    public function getBaseIcon()
    {
        return $this->response['result']['baseIcon'];
    }

    public function getDynamicIcon()
    {
        return $this->response['result']['dynamicIcon'];
    }

    public function getIcon250()
    {
        return $this->response['result']['icon250'];
    }

    public function getIcon500()
    {
        return $this->response['result']['icon500'];
    }

    public function getLibraryVersion()
    {
        return $this->response['result']['libraryVersion'];
    }

    public function isProtected()
    {
        return $this->response['result']['isProtected'];
    }

    public function getType()
    {
        return $this->response['result']['type'];
    }

    public function getIcon()
    {
        return $this->response['result']['icon'];
    }

    public function toArray(): array
    {
        return $this->response;
    }
}
