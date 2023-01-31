<?php
namespace verbb\auth\helpers;

use Craft;
use craft\helpers\StringHelper;

class UrlHelper
{
    // Static Methods
    // =========================================================================

    public static function normalizeBaseUri(string $uri)
    {
        $uri = rtrim($uri, '/');
        $path = parse_url($uri)['path'] ?? null;

        // Check if this is a path, or a file, only append a trailing slash if path
        if ($path) {
            $segments = explode('/', $path);
            $lastSegment = end($segments);

            if (str_contains($lastSegment, '.')) {
                return $uri;
            }
        }

        return $uri . '/';
    }
}
