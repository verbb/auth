<?php
namespace verbb\auth\models;

use Craft;
use craft\base\Model;
use craft\base\PluginInterface;

use League\OAuth1\Client\Credentials\TokenCredentials as OAuth1Token;
use League\OAuth2\Client\Token\AccessToken as OAuth2Token;

use DateTime;

class Token extends Model
{
    // Constants
    // =========================================================================

    public const TOKEN_TYPE_OAUTH1 = 'oauth1';
    public const TOKEN_TYPE_OAUTH2 = 'oauth2';


    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?string $ownerHandle = null;
    public ?string $providerType = null;
    public ?string $tokenType = null;
    public ?string $reference = null;
    public ?string $accessToken = null;
    public ?string $secret = null;
    public ?string $expires = null;
    public ?string $refreshToken = null;
    public ?string $resourceOwnerId = null;
    public ?array $values = null;
    public ?DateTime $dateCreated = null;
    public ?DateTime $dateUpdated = null;
    public ?string $uid = null;

    private OAuth1Token|OAuth2Token|null $_token = null;


    // Public Methods
    // =========================================================================

    public function getPlugin(): ?PluginInterface
    {
        return Craft::$app->getPlugins()->getPlugin($this->ownerHandle);
    }

    public function setToken(OAuth1Token|OAuth2Token|null $value): void
    {
        $this->_token = $value;
    }

    public function getToken(): OAuth1Token|OAuth2Token|null
    {
        if ($this->_token) {
            return $this->_token;
        }

        // OAuth1 token
        if ($this->tokenType === self::TOKEN_TYPE_OAUTH1) {
            $realToken = new OAuth1Token();
            $realToken->setIdentifier($this->accessToken);
            $realToken->setSecret($this->secret);

            return $this->_token = $realToken;
        }

        // OAuth2 token
        if ($this->tokenType === self::TOKEN_TYPE_OAUTH2) {
            return $this->_token = new OAuth2Token(array_merge($this->values, [
                'access_token' => $this->accessToken,
                'refresh_token' => $this->refreshToken,
                'expires' => $this->expires,
                'resource_owner_id' => $this->resourceOwnerId,
            ]));
        }

        return null;
    }
}
