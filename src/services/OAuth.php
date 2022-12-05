<?php
namespace verbb\auth\services;

use verbb\auth\Auth;
use verbb\auth\base\OAuthProviderInterface;
use verbb\auth\events\AccessTokenEvent;
use verbb\auth\events\AuthorizationUrlEvent;
use verbb\auth\models\Token;

use Craft;
use craft\base\Component;

use yii\web\Response;

class OAuth extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_BEFORE_AUTHORIZATION_REDIRECT = 'beforeAuthorizationRedirect';
    public const EVENT_BEFORE_FETCH_ACCESS_TOKEN = 'beforeFetchAccessToken';
    public const EVENT_AFTER_FETCH_ACCESS_TOKEN = 'afterFetchAccessToken';


    // Public Methods
    // =========================================================================

    public function connect(string $ownerHandle, OAuthProviderInterface $provider): Response
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

        return Craft::$app->getResponse()->redirect($event->authUrl);
    }

    public function callback(string $ownerHandle, OAuthProviderInterface $provider): Token
    {
        // Allow plugins to modify the Authorization URL
        $event = new AccessTokenEvent([
            'provider' => $provider,
            'ownerHandle' => $ownerHandle,
        ]);

        $this->trigger(self::EVENT_BEFORE_FETCH_ACCESS_TOKEN, $event);
        
        // Get the OAuth Access Token, depending on OAuth version
        $accessToken = $provider->getAccessToken();

        // Save the access token to the database, and return a Token model
        $token = Auth::$plugin->getTokens()->createToken($ownerHandle, $provider, $accessToken);

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
}
