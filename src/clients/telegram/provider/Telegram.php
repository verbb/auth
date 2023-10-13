<?php
namespace verbb\auth\clients\telegram\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface;

class Telegram extends AbstractProvider
{
    use BearerAuthorizationTrait;
    
    protected $clientId;
    protected $redirectUri;
    protected $clientSecret;
    protected $params;
    
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://oauth.telegram.org/auth';
    }
    
    public function getBaseAccessTokenUrl(array $params): string
    {
        return '';
    }
    
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return '';
    }
    
    public function getAccessToken($grant, array $options = []): ?AccessToken
    {
        if (!isset($options['code']))
        {
            return null;
        }

        $this->params = json_decode(base64_decode($options['code']), true);

        if (!$this->params)
        {
            return null;
        }
        
        $auth_data = $this->params;

        if (!isset($auth_data['hash']))
        {
            return null;
        }

        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);
        $data_check_arr = [];

        foreach ($auth_data as $key => $value) 
        {
            $data_check_arr[] = $key . '=' . $value;
        }

        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $this->clientSecret, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);
        
        if (strcmp($hash, $check_hash) !== 0) 
        {
            return null;
        }

        if ((time() - $auth_data['auth_date']) > 86400) 
        {
            return null;
        }
        
        $options['access_token'] = $this->params['hash'];
        $prepared = $this->prepareAccessTokenResponse($options);

        return new AccessToken($prepared);
    }
    
    protected function getAllowedClientOptions(array $options): array
    {
        $this->clientId = $options['clientId'];
        $this->redirectUri = $options['redirectUri'];
        $this->clientSecret = $options['clientSecret'];

        return $options;
    }
    
    protected function getAuthorizationParameters(array $options): array
    {
        $options['bot_id'] = $this->clientId;
        $options['embed'] = 1;
        $options['request_access'] = 'write';
        $options['return_to'] = $this->redirectUri;
        $options['origin'] = 'https://' . parse_url($this->redirectUri)['host'];

        return $options;
    }

    public function getDefaultScopes(): array
    {
        return [];
    }
    
    protected function getScopeSeparator(): string
    {
        return ' ';
    }
    
    public function getResourceOwner(AccessToken $token): TelegramUser
    {
        return $this->createResourceOwner([], $token);
    }
    
    public function createResourceOwner(array $response, AccessToken $token): TelegramUser
    {
        return new TelegramUser($this->params);
    }

    public function checkResponse(Response $response, $data)
    {
        
    }
    
}