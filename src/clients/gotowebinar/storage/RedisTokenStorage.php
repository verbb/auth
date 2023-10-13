<?php
namespace verbb\auth\clients\gotowebinar\storage;

use function GuzzleHttp\json_encode;
use DalPraS\OAuth2\Client\Decorators\AccessTokenDecorator;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use function json_decode;
use Predis\Client;
use Redis;
use RedisException;

class RedisTokenStorage implements TokenStorageInterface {
    
    /**
     * @var Redis|Client
     */
    private Redis|Client $redis;
    
    /**
     * @param Redis|Client $redis
     */
    public function __construct(Client|Redis $redis) {
        $this->redis = $redis;
    }
 
    /**
     * Recupera un accessToken da redis.
     * Viene usato l'accessToken corrispondente all'organizerKey settato.
     *
     * @see \DalPraS\OAuth2\Client\Storage\TokenStorageInterface::fetchToken()
     * @param string $organizerKey
     * @return AccessToken|NULL
     */
    public function fetchToken(string $organizerKey): ?AccessToken
    {
        $id = sprintf(self::STORAGE_DOMAIN, $organizerKey);
        // controllo che il token sia stato salvato in redis
        if ($this->redis->exists($id)) {
            $data = json_decode($this->redis->get($id), true);
            if ( !empty($data) ) {
                return new AccessToken($data);
            }
        }
        return null;
    }

    /**
     * Save the accessToken with the specified id.
     * Set an expiration of 365 days for the id saved in redis (cleanup in redis).
     *
     * @param AccessToken $accessToken
     * @return RedisTokenStorage
     * @throws RedisException
     */
    public function saveToken(AccessToken $accessToken): static
    {
        $organizerKey = (new AccessTokenDecorator($accessToken))->getOrganizerKey();
        $id = sprintf(self::STORAGE_DOMAIN, $organizerKey);
        
        // Store token for future usage
        $this->redis->set($id, \json_encode($accessToken->jsonSerialize()));
        $this->redis->expireAt($id, time() + (86400 * 365));
        return $this;
    }
}

