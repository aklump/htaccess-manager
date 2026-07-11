# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- Nothing to list

## [0.0.10] - 2026-07-11

### Added

- **Ban Wordpress**: Added support for fast PHP-based error responses using the `redirects.error_handlers` strategy. When configured, it now generates `_handle-404.php` and uses `RewriteRule` for more efficient blocking of WordPress paths.
- **Testing**: Significant increase in test coverage across the project, including new unit tests for `PluginFailedException`, `Path`, `SubstituteEnvVars`, `FileHeader`, and `MergePluginSchemas`.

### Changed

- Standardized regex pattern generation in `BanWordpressPlugin` to ensure consistency between `RedirectMatch` and `RewriteRule` outputs.

### Fixed

- **Filesystem**: Added missing `AKlump\HtaccessManager\Filesystem\Path::getExtension()` method to safely extract file extensions.

## [0.0.9] - 2026-02-16

### Added

- Redirect fast responses using `redirects.error_handlers` to handle situations where Apache would pass error handling to index.php. This prevents unnecessary application bootstrap/overhead when handling errors.
- Support for using environment variables in config. Thanks for the idea @RobLoach https://github.com/aklump/htaccess-manager/issues/1

### Fixed

- Some issues with leading slashes in redirect patterns.

## [0.0.7] - 2025-11-21

### Added

- Support for disabling path processing using @ or # delimiters; see README.md
- SecureConfig plugin to enhance security by blocking access to sensitive configuration files
- More Wordpress paths to the Ban Wordpress plugin.

### Changed

- lorem
- 🚨BREAKING CHANGE! lorem

### Deprecated

- lorem

### Removed

- lorem

### Fixed

- lorem

### Security

- lorem

## [0.0.5] - 2025-01-01

### Added

- Support for Composer < 2.2
