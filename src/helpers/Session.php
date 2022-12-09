<?php
namespace verbb\auth\helpers;

use Craft;
use craft\helpers\StringHelper;

class Session
{
    // Static Methods
    // =========================================================================

    public static function get(string $key)
    {
        return Craft::$app->getSession()->get("verbb-auth.{$key}");
    }

    public static function set(string $key, mixed $value): void
    {
        Craft::$app->getSession()->set("verbb-auth.{$key}", $value);
    }

    public static function remove(string $key): void
    {
        Craft::$app->getSession()->remove("verbb-auth.{$key}");
    }

    public static function setFlash(string $namespace, string $key, mixed $value, bool $removeAfterAccess = true): void
    {
        $session = Craft::$app->getSession();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            // Use the regular calls for CP flashes to ensure they are picked up by the toast
            $method = StringHelper::toCamelCase('set' . $key);
            $session->$method($value);

            // Still show our custom flash
            $key = "$namespace:cp-$key";
        } else {
            $key = "$namespace:$key";
        }

        Craft::$app->getSession()->setFlash($key, $value, $removeAfterAccess);
    }

    public static function getFlash(string $namespace, string $key, mixed $defaultValue = null, bool $delete = false): mixed
    {
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $key = "$namespace:cp-$key";
        } else {
            $key = "$namespace:$key";
        }

        return Craft::$app->getSession()->getFlash($key, $defaultValue, $delete);
    }

    public static function setError(string $namespace, string $message, bool $forceCp = false): void
    {
        self::setFlash($namespace, 'error', $message);

        // This is mostly for when throwing an error in a callback, which is redirected to the control panel origin
        // However, because callback's are typically a site request (for easier routing, `usePathInfo`), the regular
        // flash calls won't work, as they check for `getIsCpRequest()`
        if ($forceCp) {
            self::setNotificationFlash('error', $message, [
                'icon' => 'alert',
                'iconLabel' => Craft::t('app', 'Error'),
            ]);
        }
    }

    public static function setNotice(string $namespace, string $message, bool $forceCp = false): void
    {
        self::setFlash($namespace, 'notice', $message);

        if ($forceCp) {
            self::setNotificationFlash('notice', $message, [
                'icon' => 'info',
                'iconLabel' => Craft::t('app', 'Notice'),
            ]);
        }
    }

    public static function setSuccess(string $namespace, string $message, bool $forceCp = false): void
    {
        self::setFlash($namespace, 'success', $message);

        if ($forceCp) {
            self::setNotificationFlash('success', $message, [
                'icon' => 'check',
                'iconLabel' => Craft::t('app', 'Success'),
            ]);
        }
    }

    public static function getError(string $namespace): mixed
    {
        return self::getFlash($namespace, 'error');
    }

    public static function getNotice(string $namespace): mixed
    {
        return self::getFlash($namespace, 'notice');
    }

    public static function getSuccess(string $namespace): mixed
    {
        return self::getFlash($namespace, 'success');
    }

    public static function setNotificationFlash(string $type, string $message, array $settings = []): void
    {
        Craft::$app->getSession()->setFlash("cp-notification-$type", [$message, $settings]);
    }
}
