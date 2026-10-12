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
        // Provider instances are reused; a failed attempt must not retain an identity.
        $this->params = null;

        if (!is_string($options['code'] ?? null)) {
            return null;
        }

        $params = json_decode(base64_decode($options['code']), true, 512, JSON_BIGINT_AS_STRING);

        if (!is_array($params)) {
            return null;
        }

        // Numeric identity fields cannot absorb another signed line as part of their value.
        foreach (['id', 'auth_date'] as $key) {
            $value = $params[$key] ?? null;

            if ((!is_string($value) && !is_int($value)) || preg_match('/\A[0-9]+\z/', (string)$value) !== 1) {
                return null;
            }
        }

        if (!is_string($params['hash'] ?? null) || preg_match('/\A[0-9a-f]{64}\z/', $params['hash']) !== 1) {
            return null;
        }

        foreach ($params as $value) {
            if (!is_string($value) && !is_int($value)) {
                return null;
            }
        }

        $auth_data = $params;

        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);
        $data_check_arr = [];

        foreach ($auth_data as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }

        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $this->clientSecret, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);

        if (!hash_equals($hash, $check_hash)) {
            return null;
        }

        if ((time() - $auth_data['auth_date']) > 86400) {
            return null;
        }

        $options['access_token'] = $params['hash'];
        $prepared = $this->prepareAccessTokenResponse($options);
        $token = new AccessToken($prepared);

        $this->params = $params;

        return $token;
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
        if ($this->params === null) {
            throw new IdentityProviderException('Telegram authentication has not been verified.', 0, null);
        }

        return new TelegramUser($this->params);
    }

    public function checkResponse(Response $response, $data)
    {

    }

}
