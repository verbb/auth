<?php namespace verbb\auth\clients\zendesk\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class ZendeskResourceOwner implements ResourceOwnerInterface
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
        return $this->getResponseData('user.id');
    }

    /**
     * Get user name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getResponseData('user.name');
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getResponseData('user.email');
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
