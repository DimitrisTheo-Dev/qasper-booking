# Changelog

All notable changes to this project will be documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] — 2026-06-22

### Fixed

- Settings shortcode examples now use the saved business slug instead of the old placeholder slug.

## [1.0.1] — 2026-05-30

### Fixed

- Refreshed the WordPress.org review package with the corrected contributor metadata, external-service disclosure, inline-script JSON escaping, and current WordPress compatibility metadata under a distinct plugin version.

## [1.0.0] — 2026-05-18

### Added

- Initial release.
- `[qasper_button slug label]` shortcode — first-party styled booking link, no remote script loaded.
- `[qasper_chat slug label position]` shortcode — floating Qasper chat launcher on a single page.
- Settings → Qasper Booking admin page: slug, default label, launcher position, locale override (auto/en/el/de/es/fr/it), site-wide toggle.
- Optional site-wide floating chat injection via `wp_enqueue_scripts`.
- Configurable brand accent color in Settings → Qasper Booking. The chat icon, send button, links, and the booking button take on the business's chosen color. Threaded through the widget config (`accent`) and the iframe URL.
- Shared `Qasper_Snippet_Builder::sanitize_accent()` — strict `#` + 3/6-digit hex validation; the single source of truth for accent validation across the plugin.
- Floating chat theme control (`system`, `light`, `dark`) in settings and `[qasper_chat]`. Theme is threaded only into the widget config; direct links and booking buttons stay theme-free.
- WordPress-Extra phpcs ruleset (`phpcs.xml.dist`).
- GitHub Actions CI: PHP 7.4 / 8.0 / 8.2 lint matrix + phpcs + the official WP Plugin Check action.
- GPL v3 or later license (full GPLv3 text in `LICENSE`).
- Uninstall hook removes the `qasper_booking_settings` option from `wp_options`.
