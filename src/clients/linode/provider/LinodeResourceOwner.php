<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2018 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace verbb\auth\clients\linode\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

/**
 * Linode OAuth account details.
 */
class LinodeResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /** @var array */
    protected array $response = [];

    /**
     * Creates new resource owner.
     *
     * @param array $response
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function getId()
    {
        return null;
    }

    public function toArray(): array
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->getValueByKey($this->response, 'first_name');
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->getValueByKey($this->response, 'last_name');
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->getValueByKey($this->response, 'company');
    }

    /**
     * @return array
     */
    public function getAddress(): array
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
    public function getPhone(): string
    {
        return $this->getValueByKey($this->response, 'phone');
    }

    /**
     * @return string
     */
    public function getTaxId(): string
    {
        return $this->getValueByKey($this->response, 'tax_id');
    }

    /**
     * @return string
     */
    public function getBalance(): string
    {
        return $this->getValueByKey($this->response, 'balance');
    }

    /**
     * @return array
     */
    public function getCreditCard(): array
    {
        return $this->getValueByKey($this->response, 'credit_card');
    }
}
