<?php
namespace verbb\auth\events;

use verbb\auth\base\OAuthProviderInterface;

use yii\base\Event;

class AuthorizationUrlEvent extends Event
{
    // Properties
    // =========================================================================

    public OAuthProviderInterface $provider;
    public ?string $ownerHandle = null;
    public ?string $authUrl = null;

}
