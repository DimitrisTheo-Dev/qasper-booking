=== Qasper Booking ===
Contributors:      qasperai
Tags:              booking, chat, ai, scheduling, lead-generation
Requires at least: 6.4
Tested up to:      7.0
Requires PHP:      7.4
Stable tag:        1.0.2
License:           GPLv3 or later
License URI:       https://www.gnu.org/licenses/gpl-3.0.html

Embed a Qasper booking button or AI chat widget on your WordPress site.

== Description ==

Qasper Booking lets you drop your Qasper business chat or a booking call-to-action onto any WordPress page with a shortcode, or enable a floating chat launcher site-wide from a single settings page.

= Features =

* `[qasper_button]` shortcode for a styled booking-link button.
* `[qasper_chat]` shortcode for a floating chat launcher on a single page.
* Optional site-wide floating chat (Settings → Qasper Booking).
* Widget appearance controls: brand accent color and theme (`system`, `light`, or `dark`) for floating chat.
* Locale auto-detection plus manual override for one of: English, Greek, German, Spanish, French, Italian.
* No third-party tracking from the plugin itself. The widget script is fetched from qasper.ai; nothing else loads until the visitor clicks the launcher.

== External services ==

This plugin relies on Qasper, an external service operated by Qasper at https://qasper.ai. Qasper is the booking and AI-chat platform that powers the launcher this plugin embeds; a Qasper business account is required to use it.

The service is contacted only by the `[qasper_chat]` shortcode and by the optional site-wide floating chat. The `[qasper_button]` shortcode contacts no external service — it outputs a plain first-party link.

What is sent, and when:

* When a page that uses the chat widget is loaded, the visitor's browser requests the widget script from `https://qasper.ai/embed/qasper-widget.js`. As with any externally hosted script, this request transmits the visitor's IP address and user agent to qasper.ai.
* The business slug, locale, launcher position, label, accent color, and theme you configure are passed to the widget so it knows which Qasper business to open and how to render its shell.
* Only if the visitor clicks the launcher, an iframe loads the chat from qasper.ai. From that point the visitor communicates with qasper.ai directly, and whatever they enter in the chat is sent to qasper.ai.

Nothing is sent to qasper.ai before such a page is viewed. The `[qasper_button]` shortcode sends nothing until the visitor clicks the link and is taken to qasper.ai.

Use of the Qasper service is subject to its terms and privacy policy:

* Terms of Service: https://qasper.ai/terms
* Privacy Policy: https://qasper.ai/privacy

Because the widget contacts qasper.ai, add qasper.ai to your site's privacy policy and, if you use a cookie or consent banner, list it under the categories your visitors must consent to.

== Installation ==

1. Upload `qasper-booking` to `/wp-content/plugins/` (or install via the Plugins screen).
2. Activate the plugin.
3. Visit Settings → Qasper Booking and enter your business slug (e.g. `new-york-barber`).
4. Either enable site-wide floating chat or paste a shortcode into a page.

== Frequently Asked Questions ==

= Where do I get the business slug? =

Your business slug is the URL fragment on your Qasper public chat page: `https://qasper.ai/business-agent/{slug}/chat`.

= Can I use the booking button without loading any third-party script? =

Yes. `[qasper_button]` renders a first-party `<a>` link that opens the Qasper chat in a new tab — no remote script is fetched.

= Does this plugin require an account on qasper.ai? =

Yes — you need a Qasper business profile to get a slug.

== Changelog ==

= 1.0.2 =
* Settings shortcode examples now use the saved business slug instead of the old placeholder slug.

= 1.0.1 =
* WordPress.org review package refresh: contributor metadata, external-service disclosure, inline-script JSON escaping, and current WordPress compatibility metadata are included in a distinct release package.

= 1.0.0 =
* Initial release: button shortcode, chat shortcode, site-wide floating chat, locale handling, settings page.
* Configurable brand accent color (Settings → Qasper Booking → Brand color). The chat icon, send button, links, and the booking button take on your brand color. Strict `#`-hex validation at every layer; an invalid or unset value falls back to the Qasper default.
* Floating chat theme control (`system`, `light`, or `dark`) in settings and `[qasper_chat position="right" theme="dark"]`. Booking buttons do not load the widget theme and remain plain links.
