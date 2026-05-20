=== Qasper Booking ===
Contributors:      qasper
Tags:              booking, chat, ai, scheduling, lead-generation
Requires at least: 6.4
Tested up to:      6.9
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPLv3 or later
License URI:       https://www.gnu.org/licenses/gpl-3.0.html

Embed a Qasper booking button or AI chat widget on your WordPress site.

== Description ==

Qasper Booking lets you drop your Qasper business chat or a booking call-to-action onto any WordPress page with a shortcode, or enable a floating chat launcher site-wide from a single settings page.

= Features =

* `[qasper_button]` shortcode for a styled booking-link button.
* `[qasper_chat]` shortcode for a floating chat launcher on a single page.
* Optional site-wide floating chat (Settings → Qasper Booking).
* Locale auto-detection plus manual override for one of: English, Greek, German, Spanish, French, Italian.
* No third-party tracking from the plugin itself. The widget script is fetched from qasper.ai; nothing else loads until the visitor clicks the launcher.

= Privacy notice =

When the widget loads, it fetches a small script from qasper.ai. The script does not collect personal data or set cookies. When a visitor clicks the launcher, an iframe loads chat content from qasper.ai. You must add qasper.ai to your privacy policy and, if you use a cookie banner, list it under the categories visitors must consent to.

== Installation ==

1. Upload `qasper-booking` to `/wp-content/plugins/` (or install via the Plugins screen).
2. Activate the plugin.
3. Visit Settings → Qasper Booking and enter your business slug (e.g. `berlin-barber`).
4. Either enable site-wide floating chat or paste a shortcode into a page.

== Frequently Asked Questions ==

= Where do I get the business slug? =

Your business slug is the URL fragment on your Qasper public chat page: `https://qasper.ai/business-agent/{slug}/chat`.

= Can I use the booking button without loading any third-party script? =

Yes. `[qasper_button]` renders a first-party `<a>` link that opens the Qasper chat in a new tab — no remote script is fetched.

= Does this plugin require an account on qasper.ai? =

Yes — you need a Qasper business profile to get a slug.

== Changelog ==

= 1.0.0 =
* Initial release: button shortcode, chat shortcode, site-wide floating chat, locale handling, settings page.
