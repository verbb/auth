<?php
namespace verbb\auth\base;

interface OAuthProviderInterface
{
    public function getRedirectUri(): string;
    public function getOAuthProviderClass(): string;

}
