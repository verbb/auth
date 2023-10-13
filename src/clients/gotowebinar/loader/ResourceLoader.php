<?php
namespace verbb\auth\clients\gotowebinar\loader;

use verbb\auth\clients\gotowebinar\storage\TokenStorageInterface;
use verbb\auth\clients\gotowebinar\provider\GotoWebinar;
use DalPraS\OAuth2\Client\Resources\CoOrganizer;
use DalPraS\OAuth2\Client\Resources\Session;
use DalPraS\OAuth2\Client\Resources\Attendee;
use DalPraS\OAuth2\Client\Provider\GotoWebinarResourceOwner;
use DalPraS\OAuth2\Client\Resources\Registrant;
use DalPraS\OAuth2\Client\Resources\Webinar;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Store Organizer's accessTokens in a repository.
 */
class ResourceLoader {

    /**
     * Token storage.
     *
     * @var \DalPraS\OAuth2\Client\Storage\TokenStorageInterface
     */
    private TokenStorageInterface|\DalPraS\OAuth2\Client\Storage\TokenStorageInterface $storage;

    /**
     * @var \DalPraS\OAuth2\Client\Provider\GotoWebinar
     */
    private \DalPraS\OAuth2\Client\Provider\GotoWebinar|GotoWebinar $provider;

    public function __construct(TokenStorageInterface $storage, GotoWebinar $provider) {
        $this->storage  = $storage;
        $this->provider = $provider;
    }

    /**
     * Check if the token is valid and in case refreshes the token and
     * save it in your current storage.
     *
     * @param AccessToken|null $accessToken
     * @return AccessTokenInterface|AccessToken|null
     * @throws IdentityProviderException
     */
    protected function refreshToken(?AccessToken $accessToken): AccessTokenInterface|AccessToken|null
    {
        switch (true) {
            case $accessToken === null:
                break;

            case $accessToken->hasExpired():
                $accessToken = $this->provider->getAccessToken('refresh_token', [
                    'refresh_token' => $accessToken->getRefreshToken()
                ]);
                // Purge old access token and store new access token to your data store.
                $this->storage->saveToken($accessToken);
                break;
        }
        return $accessToken;
    }

    /**
     * Get the Webinar resource
     *
     * @param string $organizerKey
     * @return Webinar|NULL
     */
    public function getWebinarResource(string $organizerKey): ?Webinar
    {
        $accessToken = $this->refreshToken($this->storage->fetchToken($organizerKey));
        return $accessToken ? (new Webinar($this->provider, $accessToken)) : null;
    }

    /**
     * Get the Registrant resource
     *
     * @param string $organizerKey
     * @return Registrant|NULL
     */
    public function getRegistrantResource(string $organizerKey): ?Registrant
    {
        $accessToken = $this->refreshToken($this->storage->fetchToken($organizerKey));
        return $accessToken ? (new Registrant($this->provider, $accessToken)) : null;
    }

    /**
     * Get the ResourceOwner using the storage with the OrganizerKey param.
     *
     * @param string $organizerKey
     * @return GotoWebinarResourceOwner|NULL
     */
    public function getResourceOwner(string $organizerKey): ?GotoWebinarResourceOwner
    {
        $accessToken = $this->refreshToken($this->storage->fetchToken($organizerKey));
        return $accessToken ? $this->provider->getResourceOwner($accessToken) : null;
    }

    /**
     * Get the Attenee resource
     *
     * @param string $organizerKey
     * @return Attendee|NULL
     */
    public function getAttendeesResource(string $organizerKey): ?Attendee
    {
        $accessToken = $this->refreshToken($this->storage->fetchToken($organizerKey));
        return $accessToken ? (new Attendee($this->provider, $accessToken)) : null;
    }

    /**
     * Get the Attenee resource
     *
     * @param string $organizerKey
     * @return Session|NULL
     */
    public function getSessionResource(string $organizerKey): ?Session
    {
        $accessToken = $this->refreshToken($this->storage->fetchToken($organizerKey));
        return $accessToken ? (new Session($this->provider, $accessToken)) : null;
    }

    /**
     * @param string $organizerKey
     * @return CoOrganizer|null
     */
    public function getOrganizerResource(string $organizerKey): ?CoOrganizer
    {
        $accessToken = $this->refreshToken($this->storage->fetchToken($organizerKey));
        return $accessToken ? (new CoOrganizer($this->provider, $accessToken)) : null;
    }

}

