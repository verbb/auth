<?php


namespace verbb\auth\clients\wechat\grant\miniprogram;

class AuthorizationCode extends \League\OAuth2\Client\Grant\AuthorizationCode
{
    protected function getRequiredRequestParameters()
    {
        return [
            'js_code',
        ];
    }
}
