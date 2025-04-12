<?php namespace verbb\auth\clients\paypal\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class PayPalResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get user id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->response['user_id'] ?: null;
    }

    /**
     * Get user name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->response['name'] ?: null;
    }

    /**
     * Get user given name
     *
     * @return string|null
     */
    public function getGivenName(): ?string
    {
        return $this->response['given_name'] ?: null;
    }

    /**
     * Get user family name
     *
     * @return string|null
     */
    public function getFamilyName(): ?string
    {
        return $this->response['family_name'] ?: null;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->response['email'] ?: null;
    }

    /**
     * Checks if user is verified
     *
     * @return boolean
     */
    public function isVerified(): bool
    {
        return $this->response['verified'] ?: false;
    }

    /**
     * Get user gender
     *
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->response['gender'] ?: null;
    }

    /**
     * Get user birthdate
     *
     * @return string|null
     */
    public function getBirthdate(): ?string
    {
        return $this->response['birthdate'] ?: null;
    }

    /**
     * Get user zoneinfo
     *
     * @return string|null
     */
    public function getZoneinfo(): ?string
    {
        return $this->response['zoneinfo'] ?: null;
    }

    /**
     * Get user locale
     *
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->response['locale'] ?: null;
    }

    /**
     * Get user phone number
     *
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->response['phone_number'] ?: null;
    }

    /**
     * Get user address
     *
     * @return array
     */
    public function getAddress(): array
    {
        return $this->response['address'] ?: [];
    }

    /**
     * Checks if user has verified account
     *
     * @return boolean
     */
    public function isVerifiedAccount(): bool
    {
        return $this->response['verified_account'] ?: false;
    }

    /**
     * Get user account type
     *
     * @return string|null
     */
    public function getAccountType(): ?string
    {
        return $this->response['account_type'] ?: null;
    }

    /**
     * Get user age range
     *
     * @return string|null
     */
    public function getAgeRange(): ?string
    {
        return $this->response['age_range'] ?: null;
    }

    /**
     * Get user payer id
     *
     * @return string|null
     */
    public function getPayerId(): ?string
    {
        return $this->response['payer_id'] ?: null;
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
}
