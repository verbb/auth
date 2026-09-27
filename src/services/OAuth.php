<?php
namespace verbb\auth\services;

use verbb\auth\Auth;
use verbb\auth\base\OAuthProviderInterface;
use verbb\auth\events\AccessTokenEvent;
use verbb\auth\events\AuthorizationUrlEvent;
use verbb\auth\models\Token;

use craft\base\Component;

use yii\web\Response;

class OAuth extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_BEFORE_AUTHORIZATION_REDIRECT = 'beforeAuthorizationRedirect';
    public const EVENT_BEFORE_FETCH_ACCESS_TOKEN = 'beforeFetchAccessToken';
    public const EVENT_AFTER_FETCH_ACCESS_TOKEN = 'afterFetchAccessToken';


    // Properties
    // =========================================================================

    private ?array $_activeTransaction = null;


    // Public Methods
    // =========================================================================

    public function connect(string $ownerHandle, OAuthProviderInterface $provider, string|int|null $reference = null, array $context = []): Response
    {
        // Get the OAuth Authorization URL, depending on OAuth version
        $authUrl = $provider->getAuthorizationUrl();
        
        // Allow plugins to modify the Authorization URL
        $event = new AuthorizationUrlEvent([
            'provider' => $provider,
            'ownerHandle' => $ownerHandle,
            'authUrl' => $authUrl,
        ]);

        $this->trigger(self::EVENT_BEFORE_AUTHORIZATION_REDIRECT, $event);

        return Auth::getInstance()->getOAuthTransactions()->begin($ownerHandle, $provider, $reference, $context, $event->authUrl);
    }

    public function prepareCallback(?string $ownerHandle = null): ?Response
    {
        return Auth::getInstance()->getOAuthTransactions()->prepare($ownerHandle);
    }

    public function claimCallback(?string $ownerHandle = null, ?string $transactionId = null): array
    {
        return $this->_activeTransaction = Auth::getInstance()->getOAuthTransactions()->claim($ownerHandle, $transactionId);
    }

    public function claimAuthorizedCallback(string $ownerHandle, callable $authorizeUser, ?string $transactionId = null): array
    {
        $this->_activeTransaction = null;

        return $this->_activeTransaction = Auth::getInstance()->getOAuthTransactions()->claimAuthorized($ownerHandle, $authorizeUser, $transactionId);
    }

    public function callback(string $ownerHandle, OAuthProviderInterface $provider, string|int|null $reference = null): Token
    {
        $transaction = $this->_activeTransaction ?? $this->claimCallback($ownerHandle);
        $this->_activeTransaction = null;
        $this->_validateTransaction($transaction, $ownerHandle, $provider, $reference);

        if (method_exists($provider, 'setOAuthTransactionData')) {
            $provider->setOAuthTransactionData($transaction['providerData']);
        }

        // Trigger an event on the provider
        $provider->beforeFetchAccessToken();

        // Allow plugins to modify the Authorization URL
        $event = new AccessTokenEvent([
            'provider' => $provider,
            'ownerHandle' => $ownerHandle,
        ]);

        $this->trigger(self::EVENT_BEFORE_FETCH_ACCESS_TOKEN, $event);
        
        // Get the OAuth Access Token, depending on OAuth version
        $accessToken = $provider->getAccessToken();

        // Create a Token model from the access model to be returned
        $token = Auth::getInstance()->getTokens()->createToken($ownerHandle, $provider, $accessToken);

        // Trigger an event on the provider
        $provider->afterFetchAccessToken($token);

        // Allow plugins to modify the access token URL
        $event = new AccessTokenEvent([
            'provider' => $provider,
            'ownerHandle' => $ownerHandle,
            'accessToken' => $accessToken,
            'token' => $token,
        ]);

        $this->trigger(self::EVENT_AFTER_FETCH_ACCESS_TOKEN, $event);

        return $event->token;
    }


    // Private Methods
    // =========================================================================

    private function _validateTransaction(array $transaction, string $ownerHandle, OAuthProviderInterface $provider, string|int|null $reference): void
    {
        if (!hash_equals($transaction['ownerHandle'] ?? '', $ownerHandle)) {
            throw new \yii\web\BadRequestHttpException('The OAuth transaction belongs to a different owner.');
        }

        if (!hash_equals($transaction['providerClass'] ?? '', get_class($provider))) {
            throw new \yii\web\BadRequestHttpException('The OAuth transaction belongs to a different provider.');
        }

        $transactionReference = $transaction['reference'] ?? null;
        $reference = $reference === null ? null : (string)$reference;

        if ($transactionReference !== $reference) {
            throw new \yii\web\BadRequestHttpException('The OAuth transaction belongs to a different target.');
        }
    }
}
