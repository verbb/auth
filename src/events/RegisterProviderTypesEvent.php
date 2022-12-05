<?php
namespace verbb\auth\events;

use yii\base\Event;

class RegisterProviderTypesEvent extends Event
{
    // Properties
    // =========================================================================

    public array $types = [];
}
