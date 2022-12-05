<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2018 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace verbb\auth\providers\linode\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

/**
 * Linode OAuth account details.
 */
class LinodeResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /** @var array */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array $response
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->getValueByKey($this->response, 'first_name');
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->getValueByKey($this->response, 'last_name');
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->getValueByKey($this->response, 'company');
    }

    /**
     * @return array
     */
    public function getAddress()
    {
        $keys = ['address_1', 'address_2', 'city', 'state', 'country', 'zip'];
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $this->getValueByKey($this->response, $key);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->getValueByKey($this->response, 'phone');
    }

    /**
     * @return string
     */
    public function getTaxId()
    {
        return $this->getValueByKey($this->response, 'tax_id');
    }

    /**
     * @return string
     */
    public function getBalance()
    {
        return $this->getValueByKey($this->response, 'balance');
    }

    /**
     * @return array
     */
    public function getCreditCard()
    {
        return $this->getValueByKey($this->response, 'credit_card');
    }
}
