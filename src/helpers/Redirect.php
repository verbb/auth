<?php
namespace verbb\auth\helpers;

use Craft;
use craft\helpers\UrlHelper;

class Redirect
{
    // Static Methods
    // =========================================================================

    public static function allowed(mixed $url): ?string
    {
        if (!is_string($url) || $url === '' || trim($url) !== $url || preg_match('/[\x00-\x20\x7F]/', $url) || str_contains($url, '\\')) {
            return null;
        }

        if (UrlHelper::isProtocolRelativeUrl($url)) {
            return null;
        }

        if (!UrlHelper::isAbsoluteUrl($url)) {
            return $url;
        }

        return self::_origin($url) !== null ? $url : null;
    }

    public static function sameOrigin(mixed $url): ?string
    {
        $url = self::allowed($url);

        if ($url === null) {
            return null;
        }

        if (!UrlHelper::isAbsoluteUrl($url)) {
            return $url;
        }

        $urlOrigin = self::_origin($url);
        $requestOrigin = self::_origin(Craft::$app->getRequest()->getHostInfo());

        return $urlOrigin !== null && $urlOrigin === $requestOrigin ? $url : null;
    }

    public static function requestBaseUrl(): string
    {
        $request = Craft::$app->getRequest();
        $basePath = trim($request->getBaseUrl(), '/');
        $url = rtrim($request->getHostInfo(), '/');

        if ($basePath !== '') {
            $url .= "/{$basePath}";
        }

        return "{$url}/";
    }

    public static function safeReferrer(mixed $url): string
    {
        return self::sameOrigin($url) ?? self::requestBaseUrl();
    }

    private static function _origin(string $url): ?array
    {
        $parts = parse_url($url);

        if (
            $parts === false ||
            !isset($parts['scheme'], $parts['host']) ||
            isset($parts['user']) ||
            isset($parts['pass'])
        ) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);

        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        return [$scheme, strtolower($parts['host']), $port];
    }
}
