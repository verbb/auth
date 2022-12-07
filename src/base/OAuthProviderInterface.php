<?php
namespace verbb\auth\base;

interface OAuthProviderInterface
{
    public function getOAuthProviderClass(): string;

}
