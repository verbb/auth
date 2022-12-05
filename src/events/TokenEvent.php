<?php
namespace verbb\auth\events;

use verbb\auth\models\Token;

use yii\base\Event;

class TokenEvent extends Event
{
    // Properties
    // =========================================================================

    public ?Token $token = null;
    public bool $isNew = false;

}
