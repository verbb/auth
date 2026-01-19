# Changelog

## 2.0.29 - 2026-01-19

### Fixed
- Fix Client Credentials grants to using `getAccessTokenOptions()` and `scopes` in request.

## 2.0.28 - 2025-11-12

### Fixed
- Fix `GithubResourceOwner` typing.

## 2.0.27 - 2025-10-31

### Fixed
- Fix Client Credentials grants to using `getAccessTokenOptions()` and `scopes` in request.
- Fix type error for `AuthorizationUrlEvent`.

## 2.0.26 - 2025-07-22

### Fixed
- Fix PSR autoloading issue for Procurios due to typo.

## 2.0.25 - 2025-07-18

### Added
- Add SuiteCRM provider.
- Add Procurios provider.
- Add Marketo provider.
- Add LiveChat provider.
- Add Intercom provider.
- Add HelpScout provider.
- Add Front provider.
- Add CleverReach provider.
- Add the ability for providers to modify the options for a request.

## 2.0.24 - 2025-06-17

### Fixed
- Fix a merge issue with `AzureResourceOwner`.

## 2.0.23 - 2025-05-15

### Added
- Add support for `psr/http-message` "^1.0 || ^2.0".
- Add Bluesky icon.

## 2.0.22 - 2025-04-24

### Added
- Add `email` for Azure provider resources for common scenarios.

## 2.0.21 - 2025-04-12

### Changed
- Update GitHub, GitLab and PayPal provider classes to be proper case.

## 2.0.20 - 2025-03-01

### Changed
- Update `league/oauth2-client` dependency with refresh token fix. Provides official compatibility with PHP 8.3+.

### Fixed
- Fix OneCRM provider.

## 2.0.19 - 2025-01-03

### Changed
- Lock `league/oauth2-client` to `2.7.0` to prevent an issue with refresh token scopes on some providers.

## 2.0.18 - 2024-10-20

### Added
- Add Xero provider.
- Add CA domain to Zoho provider.

## 2.0.17 - 2024-09-27

### Fixed
- Update handling for `getBaseApiUrl()` when a token doesn’t yet exist.

## 2.0.16 - 2024-09-26

### Fixed
- Update handling for `getBaseApiUrl()` when a token doesn’t yet exist.
- Fix an error when logging error codes.

## 2.0.15 - 2024-09-26

### Fixed
- Fix an error when making a request with a query string, and refreshing an expired token in the same request.

## 2.0.14 - 2024-09-12

### Added
- Add Microsoft Entra provider.

## 2.0.13 - 2024-08-29

### Fixed
- Fix Constant Contact client not working.

## 2.0.12 - 2024-08-27

### Fixed
- Add conditional for Twitter `code_verifier` check to prevent errors when already supplied.
- Fix an error with token values not being typed correctly.

## 2.0.11 - 2024-08-09

### Fixed
- Fix an issue with the Generic provider.

## 2.0.10 - 2024-08-09

### Fixed
- Fix an issue with the Generic provider and duplicate `baseApiUrl`.

## 2.0.9 - 2024-08-02

### Added
- Add the ability to set `baseApiUrl` for providers as part of their config. This can be a string, or a callback function.

### Fixed
- Fix Zoho base API URL not taking into account `useDeveloper` setting.

## 2.0.8 - 2024-07-17

### Changed
- Updated Amazon Cognito provider.

## 2.0.7 - 2024-07-17

### Fixed
- Fix an error for IdentityServer4 `getBaseApiUrl()`.
- Fix an error for Fedex `getBaseApiUrl()`.
- Fix errors for Amazon Cognito Provider. (thanks @lucbernard).

## 2.0.6 - 2024-07-15

### Added
- Add Amazon Cognito provider.

## 2.0.5 - 2024-06-21

### Fixed
- Fix an error with Azure/Entra with login approval.

## 2.0.4 - 2024-06-21

### Fixed
- Fix Slack provider not setting correct auth token for requests.

## 2.0.3 - 2024-05-25

### Fixed
- Fix LinkedIn client to support v2 API.

## 2.0.2 - 2024-05-24

### Fixed
- Fix LinkedIn client to support v2 API.
- Fix an error with URL generation for authenticated requests.
- Fix error handling for IdentityServer4 provider.

## 2.0.1 - 2024-05-15

### Fixed
- Fix an error with Google provider

## 2.0.0 - 2024-05-11

### Added
- Add improved session-storage and restoration between authorization and callback methods, to improve failed sessions in some cases.
- Add IdentityServer4 provider.

### Changed
- Now requires PHP `8.2.0+`.
- Now requires Craft `5.0.0+`.

### Fixed
- Fix Apple provider token.
- Fix an error with Spotify error handling.
- Fix some Salesforce provider settings.
- Fix namespace for IdentityServer4 provider.
- Fix an error with URL generation for authenticated requests.
- Fix error handling for IdentityServer4 provider.

## 1.0.43 - 2026-01-19

### Fixed
- Fix Client Credentials grants to using `getAccessTokenOptions()` and `scopes` in request.

## 1.0.42 - 2025-11-12

### Fixed
- Fix `GithubResourceOwner` typing.

## 1.0.41 - 2025-10-31

### Fixed
- Fix Client Credentials grants to using `getAccessTokenOptions()` and `scopes` in request.
- Fix type error for `AuthorizationUrlEvent`.

## 1.0.40 - 2025-07-16

### Added
- Unlock `league/oauth2-client` from `2.7.0`.

## 1.0.39 - 2025-05-15

### Added
- Add support for `psr/http-message` "^1.0 || ^2.0".

## 1.0.38 - 2025-04-24

### Added
- Add `email` for Azure provider resources for common scenarios.

## 1.0.37 - 2025-03-07

### Added
- Add Bluesky icon.

## 1.0.36 - 2025-01-03

### Changed
- Lock `league/oauth2-client` to `2.7.0` to prevent an issue with refresh token scopes on some providers.

## 1.0.35 - 2024-10-20

### Added
- Add Xero provider.
- Add CA domain to Zoho provider.

## 1.0.34 - 2024-09-13

### Fixed
- Fix an error with Microsoft Entra provider.

## 1.0.33 - 2024-09-13

### Added
- Add Microsoft Entra provider.

## 1.0.32 - 2024-08-29

### Fixed
- Fix Constant Contact client not working.

## 1.0.31 - 2024-08-27

### Fixed
- Add conditional for Twitter `code_verifier` check to prevent errors when already supplied.

## 1.0.30 - 2024-08-09

### Fixed
- Fix an issue with the Generic provider.

## 1.0.29 - 2024-08-09

### Fixed
- Fix an issue with the Generic provider and duplicate `baseApiUrl`.

## 1.0.28 - 2024-07-17

### Changed
- Updated Amazon Cognito provider.

## 1.0.27 - 2024-07-12

### Added
- Add Amazon Cognito provider.

## 1.0.26 - 2024-06-21

### Fixed
- Fix an error with Azure/Entra with login approval.

## 1.0.25 - 2024-06-21

### Fixed
- Fix Slack provider not setting correct auth token for requests.

## 1.0.24 - 2024-05-24

### Fixed
- Fix LinkedIn client to support v2 API.

## 1.0.23 - 2024-05-09

### Fixed
- Fix an error with URL generation for authenticated requests.
- Fix error handling for IdentityServer4 provider.

## 1.0.22 - 2024-04-05

### Added
- Add improved session-storage and restoration between authorization and callback methods, to improve failed sessions in some cases.

## 1.0.21 - 2024-04-04

### Fixed
- Fix Apple provider token.
- Fix an error with Spotify error handling.

## 1.0.20 - 2024-03-26

### Fixed
- Fix some Salesforce provider settings.

## 1.0.19 - 2024-03-23

### Fixed
- Fix namespace for IdentityServer4 provider.

## 1.0.18 - 2024-03-22

### Added
- Add IdentityServer4 provider.

## 1.0.17 - 2024-02-08

### Added
- Add JP Zoho data center.

### Fixed
- Fix Zoho using incorrect authorization URL for data center.

## 1.0.16 - 2024-01-30

### Added
- Add support for `multipart` request shortcut.
- Add `user-read-email` as default scope for Spotify client.

## 1.0.15 - 2023-12-18

### Fixed
- Fix `baseUri` normalization for credentials clients.

## 1.0.14 - 2023-12-17

### Fixed
- Fix requests not working correctly when either the `uri`, `url` or `baseUri` contained a dot character, or ended in a filename.

### Removed
- Removed `UrlHelper::normalizeBaseUri()` which is no longer required.

## 1.0.13 - 2023-12-17

### Added
- Add Fedex client.
- Add the ability to set the grant on providers, for providers that strictly accept only one kind.

### Fixed
- Fix incorrect base URI handling.

## 1.0.12 - 2023-12-08

### Added
- Add support for Craft’s custom Guzzle config when making requests.

### Fixed
- Fix trailing slash on `base_uri` for requests.

## 1.0.11 - 2023-11-07

### Fixed
- Fix `firebase/php-jwt` change causing issues with Apple and Azure providers.

## 1.0.10 - 2023-10-06

### Added
- Add support for LinkedIn REST APIs.
- Add `beforeFetchAccessToken` and `afterFetchAccessToken` to providers.
- Allow `uri` param for `getApiRequest()` to be an absolute URL.

## 1.0.9 - 2023-10-05

### Added
- Add FreeAgent provider.

### Changed
- Change LinkedIn to use new OpenID Connect API.

## 1.0.8 - 2023-09-13

### Added
- Add X (Twitter) helpers.

## 1.0.7 - 2023-09-07

### Added
- Add Telegram provider.

### Fixed
- Fix not returning most recent token for `getTokenByOwnerReference()`.

## 1.0.6 - 2023-05-27

### Changed
- Update PayPal client to work with latest API.

## 1.0.5 - 2023-05-17

### Changed
- Update PayPal API endpoint to support sandbox.

## 1.0.4 - 2023-04-12

### Added
- Add `defaultScopes()` to get the default scopes for clients.

## 1.0.3 - 2023-04-07

### Fixed
- Fix an error with Auth0 provider.

## 1.0.2 - 2023-04-07

### Added
- Add Neon CRM as a client.

## 1.0.1 - 2023-04-05

### Fixed
- Fix some providers’ base URI not normalizing correctly (Facebook).

## 1.0.0 - 2023-02-01

- Initial release.
