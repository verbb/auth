<?php
namespace verbb\auth\events;

use verbb\auth\base\OAuthProviderInterface;
use verbb\auth\models\Token;

use yii\base\Event;

use League\OAuth1\Client\Credentials\TokenCredentials as OAuth1Token;
use League\OAuth2\Client\Token\AccessToken as OAuth2Token;

class AccessTokenEvent extends Event
{
    // Properties
    // =========================================================================

    public OAuthProviderInterface $provider;
    public string $ownerHandle;
    public OAuth1Token|OAuth2Token $accessToken;
    public Token $token;

}
