<?php
namespace verbb\auth\services;

use verbb\auth\Auth;
use verbb\auth\base\OAuthProviderInterface;
use verbb\auth\events\TokenEvent;
use verbb\auth\models\Token;
use verbb\auth\records\Token as TokenRecord;

use Craft;
use craft\base\MemoizableArray;
use craft\db\Query;
use craft\helpers\ArrayHelper;

use yii\base\Component;

use League\OAuth1\Client\Credentials\TokenCredentials as OAuth1Token;
use League\OAuth2\Client\Token\AccessToken as OAuth2Token;

class Tokens extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_BEFORE_SAVE_TOKEN = 'beforeSaveToken';
    public const EVENT_AFTER_SAVE_TOKEN = 'afterSaveToken';
    public const EVENT_BEFORE_DELETE_TOKEN = 'beforeDeleteToken';
    public const EVENT_AFTER_DELETE_TOKEN = 'afterDeleteToken';


    // Properties
    // =========================================================================

    private ?MemoizableArray $_tokens = null;


    // Public Methods
    // =========================================================================

    public function getAllTokens(): array
    {
        return $this->_tokens()->all();
    }

    public function getAllPluginTokens(string $ownerHandle): array
    {
        return $this->_tokens()->where('ownerHandle', $ownerHandle, true)->all();
    }

    public function getAllTokensByPluginReference(string $ownerHandle, string $reference): array
    {
        return ArrayHelper::where($this->getAllPluginTokens($ownerHandle), 'reference', $reference);
    }

    public function getTokenById(int $id): ?Token
    {
        return $this->_tokens()->firstWhere('id', $id);
    }

    public function getTokenByPluginReference(string $ownerHandle, string $reference): ?Token
    {
        return ArrayHelper::firstValue($this->getAllTokensByPluginReference($ownerHandle, $reference));
    }

    public function createToken(string $ownerHandle, OAuthProviderInterface $provider, OAuth1Token|OAuth2Token $accessToken): Token
    {
        $token = new Token();
        $token->ownerHandle = $ownerHandle;
        $token->providerType = get_class($provider);

        $token->setToken($accessToken);

        if ($accessToken instanceof OAuth2Token) {
            $token->tokenType = Token::TOKEN_TYPE_OAUTH2;
            $token->accessToken = $accessToken->getToken();
            $token->expires = $accessToken->getExpires();
            $token->refreshToken = $accessToken->getRefreshToken();
            $token->resourceOwnerId = $accessToken->getResourceOwnerId();
            $token->values = $accessToken->getValues();
        } else {
            $token->tokenType = Token::TOKEN_TYPE_OAUTH1;
            $token->accessToken = $accessToken->getIdentifier();
            $token->secret = $accessToken->getSecret();
        }

        return $token;
    }

    public function upsertToken(Token $token): bool
    {
        // The defining characteristics of a token are the `ownerHandle`, `providerType`, `tokenType` and `reference`
        // So check against those an any existing record (if `id` is null) and use that token instead to save.
        if (!$token->id) {
            $matchedToken = TokenRecord::findOne([
                'ownerHandle' => $token->ownerHandle,
                'providerType' => $token->providerType,
                'tokenType' => $token->tokenType,
                'reference' => $token->reference,
            ]);

            if ($matchedToken) {
                $token->id = $matchedToken->id;
            }
        }

        return $this->saveToken($token);
    }

    public function refreshToken(Token $token, OAuth1Token|OAuth2Token $accessToken): bool
    {
        $token->accessToken = $accessToken->getToken();
        $token->expires = $accessToken->getExpires();
        
        if ($accessToken->getRefreshToken()) {
            $token->refreshToken = $accessToken->getRefreshToken();
        }

        return $this->saveToken($token);
    }

    public function saveToken(Token $token, bool $runValidation = true): bool
    {
        $isNewToken = !$token->id;

        // Fire a 'beforeSaveToken' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_TOKEN)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_TOKEN, new TokenEvent([
                'token' => $token,
                'isNew' => $isNewToken,
            ]));
        }

        if ($runValidation && !$token->validate()) {
            Auth::log('Token not saved due to validation error.');
            return false;
        }

        $tokenRecord = $this->_getTokenRecordById($token->id);
        $tokenRecord->ownerHandle = $token->ownerHandle;
        $tokenRecord->providerType = $token->providerType;
        $tokenRecord->tokenType = $token->tokenType;
        $tokenRecord->reference = $token->reference;
        $tokenRecord->accessToken = $token->accessToken;
        $tokenRecord->secret = $token->secret;
        $tokenRecord->expires = $token->expires;
        $tokenRecord->refreshToken = $token->refreshToken;
        $tokenRecord->resourceOwnerId = $token->resourceOwnerId;
        $tokenRecord->values = $token->values;

        $tokenRecord->save(false);

        if (!$token->id) {
            $token->id = $tokenRecord->id;
        }

        // Fire an 'afterSaveToken' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_TOKEN)) {
            $this->trigger(self::EVENT_AFTER_SAVE_TOKEN, new TokenEvent([
                'token' => $token,
                'isNew' => $isNewToken,
            ]));
        }

        return true;
    }

    public function deleteTokenById(int $tokenId): bool
    {
        $token = $this->getTokenById($tokenId);

        if (!$token) {
            return false;
        }

        return $this->deleteToken($token);
    }

    public function deleteToken(Token $token): bool
    {
        // Fire a 'beforeDeleteToken' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_TOKEN)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_TOKEN, new TokenEvent([
                'token' => $token,
            ]));
        }

        Craft::$app->getDb()->createCommand()
            ->delete('{{%auth_oauth_tokens}}', ['id' => $token->id])
            ->execute();

        // Fire an 'afterDeleteToken' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_TOKEN)) {
            $this->trigger(self::EVENT_AFTER_DELETE_TOKEN, new TokenEvent([
                'token' => $token,
            ]));
        }

        return true;
    }


    // Private Methods
    // =========================================================================

    private function _tokens(): MemoizableArray
    {
        if (!isset($this->_tokens)) {
            $tokens = [];

            foreach ($this->_createTokenQuery()->all() as $result) {
                $tokens[] = new Token($result);
            }

            $this->_tokens = new MemoizableArray($tokens);
        }

        return $this->_tokens;
    }

    private function _createTokenQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'ownerHandle',
                'providerType',
                'tokenType',
                'reference',
                'accessToken',
                'secret',
                'expires',
                'refreshToken',
                'resourceOwnerId',
                'values',
                'dateCreated',
                'dateUpdated',
                'uid',
            ])
            ->from(['{{%auth_oauth_tokens}}']);
    }

    private function _getTokenRecordById(?int $tokenId = null): TokenRecord
    {
        if ($tokenId !== null) {
            if ($tokenRecord = TokenRecord::findOne($tokenId)) {
                return $tokenRecord;
            }
        }

        return new TokenRecord();
    }

}
