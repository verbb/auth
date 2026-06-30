<?php
namespace verbb\auth\helpers;

use Craft;
use craft\helpers\App;
use craft\helpers\UrlHelper;

class RedirectUri
{
    // Static Methods
    // =========================================================================

    /**
     * @param bool $useActionInHeadless When headless mode is enabled, use the action endpoint instead of a CP URL.
     * @param bool $useCpUrlWhenDetached When `cpTrigger` is empty (detached CP), use a CP URL instead of a site URL.
     *     CP-admin OAuth plugins should leave this enabled. Front-end login plugins should disable it.
     */
    public static function getCallbackUri(
        ?string $redirectUri,
        string $path,
        bool $useActionInHeadless = false,
        bool $useCpUrlWhenDetached = true,
    ): string {
        if ($redirectUri = App::parseEnv($redirectUri)) {
            return $redirectUri;
        }

        $generalConfig = Craft::$app->getConfig()->getGeneral();
        $siteId = Craft::$app->getSites()->getCurrentSite()->id ?? Craft::$app->getSites()->getPrimarySite()->id;

        if ($useActionInHeadless && $generalConfig->headlessMode) {
            $actionTrigger = trim($generalConfig->actionTrigger, '/');
            $actionRoute = $actionTrigger ? "{$actionTrigger}/{$path}" : $path;

            return rtrim(UrlHelper::baseCpUrl(), '/') . '/' . trim($actionRoute, '/');
        }

        if ($generalConfig->headlessMode || ($useCpUrlWhenDetached && !$generalConfig->cpTrigger)) {
            return UrlHelper::cpUrl($path, null, null, $siteId);
        }

        return UrlHelper::siteUrl($path, null, null, $siteId);
    }
}
