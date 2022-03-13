<?php

/* # CHANGELOG
* All notable changes to this project will be documented in this file.

## [8.8.8] - 2018-11-15

# Changed
- Removed unused code.
- Renamed the entry point file to have the same name as the plugin.

## [8.8.7] - 2018-10-31

# Changed
- Replaced the `array_replace_recursive` with `array_merge` function, used for merging the settings.

## [8.8.6] - 2018-10-17

# Added
- Added protection mechanism against de-obfuscating non obfuscated text.
  e.g. dynamically inserted images which are lazy loaded.


## [8.8.5] - 2018-10-12

# Changed
- Code styling -- replaced spaces with tabs, etc.


## [8.8.4] - Skipped


## [8.8.3] - 2018-09-24

### Changed
- Made some fixes requested by WordPress VIP reviewing team.
- Removed initial SDK `Cache()` class code completely.
- Removed `mbstring` polyfill.
- Removed `loadHtml`, `saveHtml` and other filesystem accessing functions from HTML5Document library.
- Removed USER_AGENT usage from `util.php`.

## [8.8.1] - 2018-09-14

### Changed
- Made changes based on PHPCS and PHPMD using WordPress-VIP related rules.
- Used `wp_unslash()`, `esc_html()` and `esc_url()` accordingly.
- Addressed variable access for `$_SERVER` (validation, sanitization).
- Added additional comments to explain the usage of the `cache.php` file.
- Replaced spaces with tabs where needed.
- Used spaces according to the coding standard suggested by `PHPCS`.
- Used Yoda conditions.

### Removed
- Removed old code.
- Cleaned up settings file to remove unused feature flags.
- Removed files that are not used anymore.

## [8.8.0] - Skipped

## [8.7.6] - 2018-09-12
### Changed
- Updated image obfuscation to support customizable attributes name.


## [8.7.5] - 2018-09-11
### Changed
- Fixed race condition with opening/closing output buffers.


## [8.7.4] - 2018-09-04
### Added
- Started keeping track of changes inside CHANGELOG file; added some historical changes.

### Changed
- Moved amp detection in front of fetching loader
- Cleanup unused functions and feature flags


## [8.7.3] - 2018-07-27
### Changed
- Better lookup for AMP.


## [8.7.2] - Skipped
## [8.7.1] - Skipped


## [8.7.0] - 2018-07-20
### Added
- Support for image and picture tags obfuscation logic.
- Logic for adding a `noscript` tag to the page body.


## [8.6.1] - 2018-07-19
### Changes
- Fixed AMP support to work for Better AMP plugin


## [8.6.0] - 2018-07-06
### Added
- Added AMP compatibility


## [8.5.0] - 2018-07-03
### Changes
- Cleaned up WordPress plugin. Mostly coding standards modifications.


## [8.4.0] - 2018-06-05
### Added
- Added support for disabling Oriel in WP.


## [8.3.0] - 2018-05-07
### Added
- Added check for output buffer length when closing.


## [8.2.0] - 2018-03-19
### Changed
- Started using Transients API instead of cURL, if function exist.


## [8.1.0] - 2018-02-26
### Added
- Added in page messaging server side

### Changed
- Force update settings along head script
- Updated minimum version for PHP 5.3
- Updated plugin to use regex instead of using dom parser
- Updated sdk to support wp_remote_get if exists and fallback on cURL

### Removed
- Removed logs & small fixes


## [8.0.0] - 2017-06-19
### Added
- Added timeout protection mechanism

*/
