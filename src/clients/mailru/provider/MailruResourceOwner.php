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
    private $response;

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

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->response['uid'];
    }

    /**
     * User's email address
     *
     * @return string Email address
     */
    public function getEmail()
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
    public function getName()
    {
        return $this->response['first_name'] . ' ' . $this->response['last_name'];
    }

    /**
     * User's first name
     *
     * @return string First name
     */
    public function getFirstName()
    {
        return $this->response['first_name'];
    }

    /**
     * User's last name
     *
     * @return string Last name
     */
    public function getLastName()
    {
        return $this->response['last_name'];
    }

    /**
     * User's nickname
     *
     * @return string Nickname
     */
    public function getNickname()
    {
        return $this->response['nick'];
    }

    /**
     * User's profile picture url
     *
     * @return string Profile picture url
     */
    public function getImageUrl()
    {
        return ($this->response['has_pic']) ? $this->response['pic'] : '' ;
    }

    /**
     * User's gender
     *
     * @return string Gender
     */
    public function getGender()
    {
        return ($this->response['sex']) ? 'female' : 'male' ;
    }

    /**
     * User's country
     *
     * @return string Country name
     */
    public function getCountry()
    {
        return (isset($this->response['location']['country']['name']))
            ? $this->response['location']['country']['name'] : '';
    }

    /**
     * User's city
     *
     * @return string City name
     */
    public function getCity()
    {
        return (isset($this->response['location']['city']['name']))
            ? $this->response['location']['city']['name'] : '';
    }

    /**
     * User's location
     *
     * Returns city or concatenation of country and city
     *
     * @return string Location
     */
    public function getLocation()
    {
        $country = $this->getCountry();
        $city = $this->getCity();

        return (empty($country)) ? $city : $country . ', ' . $city;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->response;
    }
}
