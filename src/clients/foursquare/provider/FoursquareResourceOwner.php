<?php namespace verbb\auth\clients\foursquare\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class FoursquareResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = isset($response['response']) ? $response['response'] : [];
    }

    /**
     * Get user id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getResponseData('user.id');
    }

    /**
     * Get user first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getResponseData('user.firstName');
    }

    /**
     * Get user last name
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getResponseData('user.lastName');
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getResponseData('user.contact.email');
    }

    /**
     * Get user bio
     *
     * @return string|null
     */
    public function getBio()
    {
        return $this->getResponseData('user.bio');
    }

    /**
     * Attempts to pull value from array using dot notation.
     *
     * @param string $path
     * @param string $default
     *
     * @return mixed
     */
    protected function getResponseData($path, $default = null)
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
    public function toArray()
    {
        return $this->response;
    }
}
