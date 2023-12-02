<?php

namespace verbb\auth\clients\shopify\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class ShopifyStore implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;
    
    /**
     * @var array
     */
    protected array $response = [];

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->getValueByKey($this->response, 'shop.id');
    }

    /**
     * Get shop name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getValueByKey($this->response, 'shop.name');
    }

    /**
     * Get shop email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getValueByKey($this->response, 'shop.email');
    }

    /**
     * Get shop domain name.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->getValueByKey($this->response, 'shop.domain');
    }

    /**
     * Get shop country.
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->getValueByKey($this->response, 'shop.country_name');
    }

    /**
     * Get shop owner name.
     *
     * @return string|null
     */
    public function getShopOwner(): ?string
    {
        return $this->getValueByKey($this->response, 'shop.shop_owner');
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getValueByKey($this->response, 'shop');
    }
}
