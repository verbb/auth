<?php

namespace verbb\auth\clients\mailru\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class MailruResourceOwner implements ResourceOwnerInterface
{
    /**
     * Response
     *
     * @var array
     */
    private mixed $response;

    /**
     * Class constructor
     *
     * @param array $response
     * @return void
     */
    public function __construct(array $response)
    {
        $this->response = $response[0];
    }

    public function getId()
    {
        return $this->response['uid'];
    }

    /**
     * User's email address
     *
     * @return string Email address
     */
    public function getEmail(): string
    {
        return $this->response['email'];
    }

    /**
     * User's full name.
     *
     * Concatenated from first name and last name
     *
     * @return string Full name
     */
    public function getName(): string
    {
        return $this->response['first_name'] . ' ' . $this->response['last_name'];
    }

    /**
     * User's first name
     *
     * @return string First name
     */
    public function getFirstName(): string
    {
        return $this->response['first_name'];
    }

    /**
     * User's last name
     *
     * @return string Last name
     */
    public function getLastName(): string
    {
        return $this->response['last_name'];
    }

    /**
     * User's nickname
     *
     * @return string Nickname
     */
    public function getNickname(): string
    {
        return $this->response['nick'];
    }

    /**
     * User's profile picture url
     *
     * @return string Profile picture url
     */
    public function getImageUrl(): string
    {
        return ($this->response['has_pic']) ? $this->response['pic'] : '' ;
    }

    /**
     * User's gender
     *
     * @return string Gender
     */
    public function getGender(): string
    {
        return ($this->response['sex']) ? 'female' : 'male' ;
    }

    /**
     * User's country
     *
     * @return string Country name
     */
    public function getCountry(): string
    {
        return $this->response['location']['country']['name'] ?? '';
    }

    /**
     * User's city
     *
     * @return string City name
     */
    public function getCity(): string
    {
        return $this->response['location']['city']['name'] ?? '';
    }

    /**
     * User's location
     *
     * Returns city or concatenation of country and city
     *
     * @return string Location
     */
    public function getLocation(): string
    {
        $country = $this->getCountry();
        $city = $this->getCity();

        return (empty($country)) ? $city : $country . ', ' . $city;
    }

    public function toArray()
    {
        return $this->response;
    }
}
