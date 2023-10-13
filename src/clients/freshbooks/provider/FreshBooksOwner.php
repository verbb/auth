<?php
/**
 * Created by PhpStorm.
 * User: aaflalo
 * Date: 18-05-29
 * Time: 14:26
 */

namespace verbb\auth\clients\freshbooks\provider;

use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class FreshBooksOwner implements ResourceOwnerInterface
{

    /**
     * @var array
     */
    protected mixed $response;
    /**
     * @var AccessToken
     */
    private AccessToken $accessToken;


    /**
     * FreshBookOwner constructor.
     *
     * @param array       $response
     * @param AccessToken $accessToken
     */
    public function __construct(array $response, AccessToken $accessToken)
    {
        $this->response    = $response['response'];
        $this->accessToken = $accessToken;
    }


    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->response['id'];
    }

    /**
     * Email of the account
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->response['email'];
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->response['first_name'];
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->response['last_name'];
    }

    /**
     * Get first name
     *
     * @return array
     */
    public function getProfile(): array
    {
        return $this->response['profile'];
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->response;
    }

    /**
     * @return AccessToken
     */
    public function getToken(): AccessToken
    {
        return $this->accessToken;
    }
}
