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
                // Check for the dot-length, some might have version numbers like `15.23` in the last segment
                $parts = explode('.', $lastSegment);
                $extension = end($parts);

                // Just accept 3/4-character extensions like `php`, `html`, `json`
                if (strlen($extension) === 3 || strlen($extension) === 4) {
                    return $uri;
                }
            }
        }

        return $uri . '/';
    }
}
