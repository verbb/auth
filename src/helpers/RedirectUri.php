<?php
namespace verbb\auth\helpers;

use Craft;
use craft\helpers\App;
use craft\helpers\UrlHelper;

class RedirectUri
{
    // Static Methods
    // =========================================================================

    public static function getCallbackUri(?string $redirectUri, string $path, bool $useActionInHeadless = false): string
    {
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

        if ($generalConfig->headlessMode || !$generalConfig->cpTrigger) {
            return UrlHelper::cpUrl($path, null, null, $siteId);
        }

        return UrlHelper::siteUrl($path, null, null, $siteId);
    }
}
