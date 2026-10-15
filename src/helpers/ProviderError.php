<?php
namespace verbb\auth\helpers;

class ProviderError
{
    // Static Methods
    // =========================================================================

    /**
     * Extract known message fields without dumping arbitrary response data into logs.
     * Keep OAuth codes alongside descriptions because refresh handling uses invalid_grant.
     */
    public static function message(mixed $data, string $fallback): string
    {
        if (!is_array($data)) {
            return $fallback;
        }

        $paths = [
            'error_description', 'error.message', 'error.description', 'message', 'description',
            'error_summary', 'errors.0.detail', 'errors.0.message', 'errors.0',
            'errors', '0.message', '0.error_message', 'error', 'errorCode',
        ];

        foreach ($paths as $path) {
            $value = $data;

            foreach (explode('.', $path) as $key) {
                $value = is_array($value) ? ($value[$key] ?? null) : null;
            }

            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            $code = $data['error'] ?? null;

            if ($path === 'error_description' && is_string($code) && trim($code) !== '' && $code !== $value) {
                return $code . ': ' . $value;
            }

            return $value;
        }

        return $fallback;
    }
}
