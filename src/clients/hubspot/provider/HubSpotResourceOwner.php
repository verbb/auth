<?php
namespace verbb\auth\clients\hubspot\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class HubSpotResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected array $response = [];

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * @return array|int|string|null
     */
    public function getId(): array|int|string|null
    {
        return $this->getResponseData('user_id');
    }

    /**
     * @return array|string|null
     */
    public function getEmail(): array|string|null
    {
        return $this->getResponseData('user');
    }

    /**
     * @return array|string|null
     */
    public function getHubSpotDomain(): array|string|null
    {
        return $this->getResponseData('hub_domain');
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

    public function toArray(): array
    {
        return $this->response;
    }
}
