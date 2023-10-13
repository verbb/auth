<?php
namespace verbb\auth\clients\gotowebinar\storage;

use League\OAuth2\Client\Token\AccessToken;

class SessionTokenStorage implements TokenStorageInterface {
    
    /**
     * FIXME not available yet
     * 
     * {@inheritDoc}
     * @see \DalPraS\OAuth2\Client\Storage\TokenStorageInterface::fetchToken()
     */
    public function fetchToken(string $organizerKey)
    {
        // TODO Auto-generated method stub
        
    }

    /**
     * FIXME not available yet
     * 
     * {@inheritDoc}
     * @see \DalPraS\OAuth2\Client\Storage\TokenStorageInterface::saveToken()
     */
    public function saveToken(AccessToken $accessToken)
    {
        // TODO Auto-generated method stub
        
    }

}

