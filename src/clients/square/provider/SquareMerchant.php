<?php

namespace verbb\auth\clients\square\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class SquareMerchant implements ResourceOwnerInterface
{
    /**
     * @var string
     */
    protected mixed $uid = '';

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $email = '';

    /**
     * @var string
     */
    protected string $county_code = '';

    /**
     * @var string
     */
    protected string $language_code = '';

    /**
     * @var string
     */
    protected string $currency_code = '';

    /**
     * @var string
     */
    protected string $business_name = '';

    /**
     * @var string
     */
    protected string $business_address = '';

    /**
     * @var string
     */
    protected string $business_phone = '';

    /**
     * @var string
     */
    protected string $business_type = '';

    /**
     * @var string
     */
    protected string $shipping_address = '';

    /**
     * @var string
     */
    protected string $account_type = '';

    /**
     * @var string
     */
    protected string $account_capabilities = '';

    /**
     * @var string
     */
    protected string $location_details = '';

    /**
     * @var string
     */
    protected string $market_url = '';

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        if (!empty($attributes['id'])) {
            $this->uid = $attributes['id'];
        }

        $attributes = array_intersect_key($attributes, $this->toArray());
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->language_code;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }

    /**
     * @return string
     */
    public function getBusinessName(): string
    {
        return $this->business_name;
    }

    /**
     * @return string
     */
    public function getBusinessAddress(): string
    {
        return $this->business_address;
    }

    /**
     * @return string
     */
    public function getBusinessPhone(): string
    {
        return $this->business_phone;
    }

    /**
     * @return string
     */
    public function getBusinessType(): string
    {
        return $this->business_type;
    }

    /**
     * @return string
     */
    public function getShippingAddress(): string
    {
        return $this->shipping_address;
    }

    /**
     * @return string
     */
    public function getAccountType(): string
    {
        return $this->account_type;
    }

    /**
     * @return string
     */
    public function getAccountCapabilities(): string
    {
        return $this->account_capabilities;
    }

    /**
     * @return string
     */
    public function getLocationDetails(): string
    {
        return $this->location_details;
    }

    /**
     * @return string
     */
    public function getMarketUrl(): string
    {
        return $this->market_url;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
