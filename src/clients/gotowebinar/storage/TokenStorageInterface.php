<?php
namespace verbb\auth\clients\gotowebinar\storage;

use League\OAuth2\Client\Token\AccessToken;

interface TokenStorageInterface {
    
    /**
     * The Domain used for storing the information in redis.
     *
     * @var string
     */
    public const STORAGE_DOMAIN = 'G2W_TOKEN_%s';
    
    /**
     * Fetch the access token for a given organizer with. 
     * 
     * @param string $organizerKey
     */
    public function fetchToken(string $organizerKey);
    
    /**
     * Store a token for the current organizer.
     * 
     * @param AccessToken $accessToken
     */
    public function saveToken(AccessToken $accessToken);
    
}

