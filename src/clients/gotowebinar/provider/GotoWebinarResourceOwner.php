<?php

namespace verbb\auth\clients\gotowebinar\provider;

use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Classes implementing `ResourceOwnerInterface` may be used to represent
 * the resource owner authenticated with a service provider.
 */
class GotoWebinarResourceOwner implements ResourceOwnerInterface 
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * Creates new resource owner.
     * 
     * @example from docs
     * [
     *  [key] (string)           =>	"Numeric value assigned within the LogMeIn system for a user-product pairing."
     *  [accountKey] (string)    =>	"Numeric value assigned within the LogMeIn system for a company account."
     *  [email] (string)         =>	"The email for this user in the system."
     *  [firstName] (string)     =>	"User's first name"
     *  [lastName] (string)      =>	"User's last name"
     *  [locale] (code list)     => "User's geographical locale"
     *  [adminRoles] (role list) => "The admin roles, if any, assigned to this user for this account."
     *  [accounts] (header)      =>	"A header for additional products this user is licensed for"
     *  [name] (string)          =>	"Account name created by user at time of account creation."
     *  [adminRoles] (role list) => "The admin roles, if any, assigned to this user for this account."
     * ]
     * 
     * @example from real case
     * [
     *  [key] => 5226234234256588012, 
     *  [accountKey] => 3573452345454655708, 
     *  [email] => myname@company.com, 
     *  [firstName] => Company, 
     *  [lastName] => Training, 
     *  [locale] => it_IT, 
     *  [timeZone] => Europe/Amsterdam, 
     *  [adminRoles] => [
     *      [0] => MANAGE_SETTINGS, 
     *      [1] => MANAGE_SEATS, 
     *      [2] => MANAGE_DEVICE_GROUPS, 
     *      [3] => MANAGE_GROUPS, 
     *      [4] => MANAGE_SETTINGS_PROFILES, 
     *      [5] => SUPER_USER, 
     *      [6] => RUN_REPORTS, 
     *      [7] => MANAGE_USERS
     *  ], 
     *  [accounts] => [
     *      [0] => [
     *          [key] => 3573263565346546368, 
     *          [name] => Company, 
     *          [adminRoles] => [
     *              [0] => SUPER_USER, 
     *              [1] => MANAGE_USERS, 
     *              [2] => MANAGE_SEATS, 
     *              [3] => MANAGE_SETTINGS, 
     *              [4] => MANAGE_GROUPS, 
     *              [5] => RUN_REPORTS, 
     *              [6] => MANAGE_DEVICE_GROUPS, 
     *              [7] => MANAGE_SETTINGS_PROFILES
     *           ]
     *        ]
     *   ], 
     *   [createTime] => 1506076497748, 
     *   [products] => [[0] => G2W, [1] => G2M]
     * ]
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner key
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->getKey();
    }

    /**
     * Get resource owner key
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->getValueByKey($this->response, 'key');
    }

    /**
     * Get resource owner Account Key
     *
     * @return string
     */
    public function getAccountKey(): string
    {
        return $this->getValueByKey($this->response, 'accountKey');
    }

    /**
     * Get resource owner Email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * Get resource owner FirstName
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->getValueByKey($this->response, 'firstName');
    }

    /**
     * Get resource owner LastName
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->getValueByKey($this->response, 'lastName');
    }

    /**
     * Get resource owner locale
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->getValueByKey($this->response, 'locale');
    }

    /**
     * Get resource owner creation
     *
     * @return string
     */
    public function getCreateTime(): string
    {
        return $this->getValueByKey($this->response, 'createTime');
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
