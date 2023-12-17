# Changelog

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
