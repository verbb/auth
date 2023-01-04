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

        // Check if this is a path, or a file, only append a trailing slash if path
        return str_contains(basename($uri), '.') ? $uri : $uri . '/';
    }
}
