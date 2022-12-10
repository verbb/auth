<?php
namespace verbb\auth\base;

interface OAuthProviderInterface
{
    public static function getOAuthProviderClass(): string;

}
