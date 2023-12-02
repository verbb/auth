<?php 

namespace verbb\auth\clients\mailchimp\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class MailchimpResourceOwner implements ResourceOwnerInterface
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
        return $this->getResponseData('login_id');
    }

    /**
     * Get account id
     *
     * @return string|null
     */
    public function getAccountId(): ?string
    {
        return $this->getResponseData('account_id');
    }

    /**
     * Get account name
     *
     * @return string|null
     */
    public function getAccountName(): ?string
    {
        return $this->getResponseData('account_name');
    }

    /**
     * Get account email
     *
     * @return string|null
     */
    public function getAccountEmail(): ?string
    {
        return $this->getResponseData('name');
    }

    /**
     * Get user role
     *
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->getResponseData('role');
    }

    /**
     * Attempts to pull value from array using dot notation.
     *
     * @param string $path
     * @param string|null $default
     *
     * @return mixed
     */
    protected function getResponseData(string $path, string $default = null): mixed
    {
        return $this->getValueByKey($this->response, $path, $default);
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
