<?php

namespace verbb\auth\providers\square\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class SquareMerchant implements ResourceOwnerInterface
{
    /**
     * @var string
     */
    protected $uid;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $county_code;

    /**
     * @var string
     */
    protected $language_code;

    /**
     * @var string
     */
    protected $currency_code;

    /**
     * @var string
     */
    protected $business_name;

    /**
     * @var string
     */
    protected $business_address;

    /**
     * @var string
     */
    protected $business_phone;

    /**
     * @var string
     */
    protected $business_type;

    /**
     * @var string
     */
    protected $shipping_address;

    /**
     * @var string
     */
    protected $account_type;

    /**
     * @var string
     */
    protected $account_capabilities;

    /**
     * @var string
     */
    protected $location_details;

    /**
     * @var string
     */
    protected $market_url;

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
    public function getId()
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->language_code;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currency_code;
    }

    /**
     * @return string
     */
    public function getBusinessName()
    {
        return $this->business_name;
    }

    /**
     * @return string
     */
    public function getBusinessAddress()
    {
        return $this->business_address;
    }

    /**
     * @return string
     */
    public function getBusinessPhone()
    {
        return $this->business_phone;
    }

    /**
     * @return string
     */
    public function getBusinessType()
    {
        return $this->business_type;
    }

    /**
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->shipping_address;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->account_type;
    }

    /**
     * @return array
     */
    public function getAccountCapabilities()
    {
        return $this->account_capabilities;
    }

    /**
     * @return string
     */
    public function getLocationDetails()
    {
        return $this->location_details;
    }

    /**
     * @return string
     */
    public function getMarketUrl()
    {
        return $this->market_url;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
