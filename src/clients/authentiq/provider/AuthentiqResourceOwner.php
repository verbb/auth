<?php
/**
 * Created by alexkeramidas for Authentiq B.V.
 * User: alexkeramidas
 * Date: 14/3/2017
 * Time: 8:28 μμ
 */

namespace verbb\auth\clients\authentiq\provider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class AuthentiqResourceOwner implements ResourceOwnerInterface
{

    /**
     * Response payload
     *
     * @var array
     */

    protected mixed $data;

    /**
     * AuthentiqResourceOwner constructor.
     */
    public function __construct($data = [])
    {
        $this->data =$data;
    }

    /**
     * Retrieves id of resource owner.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->claim('sub');
    }

    /**
     * Retrieves first name of resource owner.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->claim('given_name');
    }

    /**
     * Retrieves last name of resource owner.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->claim('family_name');
    }

    /**
     * Returns a field from the parsed JWT data.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function claim(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Returns all the data obtained about the user.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}