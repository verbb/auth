<?php
namespace verbb\auth\events;

use verbb\auth\models\Token;

use Throwable;
use yii\base\Event;

class TokenEvent extends Event
{
    // Properties
    // =========================================================================

    public ?Token $token = null;
    public bool $isNew = false;

    /** Set on `Tokens::EVENT_TOKEN_REFRESH_FAILED` when the refresh attempt throws. */
    public ?Throwable $exception = null;

}
