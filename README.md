# Qasper Booking — WordPress plugin

Drop a Qasper booking button or an AI chat launcher onto any WordPress site.

## End-user installation

Download the latest [`qasper-booking-X.Y.Z.zip`](https://github.com/DimitrisTheo-Dev/qasper-booking/releases) and install via WordPress admin → **Plugins → Add New → Upload Plugin**. Activate, then visit **Settings → Qasper Booking** to enter your business slug.

For non-technical users: an end-user guide lives in [`readme.txt`](readme.txt) (WP.org format).

## Shortcodes

| Shortcode | Renders |
| --- | --- |
| `[qasper_button slug="berlin-barber" label="Book now" accent="#eea563"]` | Styled booking link, no remote script. |
| `[qasper_chat slug="berlin-barber" label="Chat with us" position="right" accent="#eea563" theme="dark"]` | Floating chat launcher on this page only. |

Site-wide floating chat: toggle in **Settings → Qasper Booking → Site-wide floating chat**. Theme is supported for floating chat only (`system`, `light`, `dark`); direct booking buttons stay as first-party links.

## Development setup

```bash
git clone https://github.com/DimitrisTheo-Dev/qasper-booking.git
cd qasper-booking
composer install
```

Lint:

```bash
vendor/bin/phpcs -p --standard=phpcs.xml.dist .
```

Syntax check on every PHP file:

```bash
find . -name '*.php' -not -path './vendor/*' -exec php -l {} \;
```

WordPress.org compliance check (uses the official Plugin Check action in CI; locally requires WP + wp-cli):

```bash
wp plugin check ../qasper-booking-X.Y.Z.zip
```

## Release process

1. Bump the version in three places: `qasper-booking.php` (plugin header `Version:` and `QASPER_BOOKING_VERSION`), `readme.txt` (`Stable tag:`), `CHANGELOG.md` (new section).
2. Commit, push, wait for CI to go green on `main`.
3. Tag: `git tag vX.Y.Z && git push --tags`.
4. Build the customer-facing zip:
   ```bash
   cd ..
   zip -r qasper-booking-X.Y.Z.zip qasper-booking \
     -x 'qasper-booking/.git/*' 'qasper-booking/.github/*' \
       'qasper-booking/composer.*' 'qasper-booking/vendor/*' \
       'qasper-booking/tests/*' \
       'qasper-booking/phpcs.xml.dist' 'qasper-booking/.phpcs.cache' \
        'qasper-booking/CONTRACT.md' 'qasper-booking/CHANGELOG.md' \
        'qasper-booking/.editorconfig' 'qasper-booking/.gitignore'
   ```
5. `gh release create vX.Y.Z qasper-booking-X.Y.Z.zip --notes-file CHANGELOG.md`.

## Cross-repo coordination

Eight runtime patterns (slug regex, locale list, widget URL, queue-init shape, agent URL base, button styles, accent validation, theme validation) are shared with the Qasper backend. See [`CONTRACT.md`](CONTRACT.md) for the discipline required to keep both sides aligned. Out-of-sync releases will break customer embeds.

## License

[GPL v3 or later](LICENSE). The plugin is free, open source, and remains so by license.
