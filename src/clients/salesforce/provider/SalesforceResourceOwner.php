<?php namespace verbb\auth\clients\salesforce\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class SalesforceResourceOwner implements ResourceOwnerInterface
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
     * @return array|string|null
     */
    public function getId(): array|string|null
    {
        return $this->getResponseData('user_id');
    }

    /**
     * Get user first name
     *
     * @return array|string|null
     */
    public function getFirstName(): array|string|null
    {
        return $this->getResponseData('first_name');
    }

    /**
     * Get user last name
     *
     * @return array|string|null
     */
    public function getLastName(): array|string|null
    {
        return $this->getResponseData('last_name');
    }

    /**
     * Get user email
     *
     * @return array|string|null
     */
    public function getEmail(): array|string|null
    {
        return $this->getResponseData('email');
    }

    /**
     * Get user title
     *
     * @return array|string|null
     */
    public function getTitle(): array|string|null
    {
        return $this->getResponseData('custom_attributes.title');
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
        $array = $this->response;

        if (!empty($path)) {
            $keys = explode('.', $path);

            foreach ($keys as $key) {
                if (isset($array[$key])) {
                    $array = $array[$key];
                } else {
                    return $default;
                }
            }
        }

        return $array;
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
