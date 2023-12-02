<?php
namespace verbb\auth\base;

use verbb\auth\models\Token;

interface ProviderInterface
{
    public function getBaseApiUrl(?Token $token): ?string;

}
