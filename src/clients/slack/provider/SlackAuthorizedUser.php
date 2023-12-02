<?php


namespace verbb\auth\clients\slack\provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Class SlackAuthorizedUser
 *
 * @package AdamPaterson\OAuth2\Client\Provider
 */
class SlackAuthorizedUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected array $response = [];

    /**
     * SlackAuthorizedUser constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->response['user_id'];
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
     * Get authorized user url
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->response['url'] ?: null;
    }

    /**
     * Get team
     *
     * @return string|null
     */
    public function getTeam(): ?string
    {
        return $this->response['team'] ?: null;
    }

    /**
     * Get user id
     *
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->response['user'] ?: null;
    }

    /**
     * Get team id
     *
     * @return string|null
     */
    public function getTeamId(): ?string
    {
        return $this->response['team_id'] ?: null;
    }

    /**
     * Get user id
     *
     * @return string|null
     */
    public function getUserId(): ?string
    {
        return $this->response['user_id'] ?: null;
    }
}
