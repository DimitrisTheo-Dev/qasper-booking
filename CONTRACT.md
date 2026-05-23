# Cross-repo coordination contract

This plugin shares **seven runtime patterns** with the Qasper backend (the private `qasper.ai` codebase). They are duplicated by design — the backend is not a build-time dependency. Any change on either side must be released in lockstep or customer embeds will break.

## The seven shared patterns

| Pattern | This repo (PHP) | Qasper backend (TypeScript) |
| --- | --- | --- |
| Slug regex `/^[a-z0-9]+(?:-[a-z0-9]+)*$/` | `includes/class-qasper-snippet-builder.php` (`SLUG_REGEX` in `is_valid_slug`) | `src/lib/embedSnippets.ts` (`SLUG_REGEX`) |
| Supported locales `[en, el, de, es, fr, it]` | `includes/class-qasper-snippet-builder.php` (`SUPPORTED_LOCALES`) | `src/lib/embedSnippets.ts` (`SUPPORTED_LOCALES`) |
| Widget script URL `https://qasper.ai/embed/qasper-widget.js` | `qasper-booking.php` (`QASPER_BOOKING_WIDGET_SCRIPT`) | `src/lib/embedSnippets.ts` (`WIDGET_SCRIPT_URL`) |
| Agent URL base `https://qasper.ai/business-agent/{slug}/chat?lang=…` | `qasper-booking.php` (`QASPER_BOOKING_AGENT_URL_BASE`) | `src/lib/embedSnippets.ts` (`buildLinkSnippet`, `buildButtonSnippet`) |
| Queue-init stub `window.QasperWidget={q:[],init:fn}` | `includes/class-qasper-snippet-builder.php::build_boot_js()` | `src/lib/embedSnippets.ts::buildScriptSnippet()` |
| Button inline CSS string | `includes/class-qasper-snippet-builder.php::build_button_html()` | `src/lib/embedSnippets.ts` (`BUTTON_INLINE_STYLES`) |
| Accent color key `accent` (validated `#` + 3/6 hex, lowercased) | `includes/class-qasper-snippet-builder.php::sanitize_accent()` | `widget/src/iframe-url.ts::normalizeAccent()` |

## Coordination discipline

- **Adding a 7th locale**: update the array on both sides in the same release window. Bump plugin minor version; ship backend locale strings the same week.
- **Changing the widget URL** (e.g. CDN move): keep `qasper.ai/embed/qasper-widget.js` as a redirect/alias for at least one release cycle. Otherwise plugins still in the wild break.
- **Changing the queue-init API**: extend, don't replace. Old plugin versions installed on customer sites cannot be force-upgraded.
- **Changing the slug regex**: must stay identical character-for-character. Any change is breaking.
- **Changing button inline CSS**: safe to update independently — the button is a first-party `<a>` with inline styles; no runtime contract.
- **Adding the `accent` key (or new config keys)**: extend, don't replace. A widget version that doesn't see `accent` silently uses its default, so the plugin can ship `accent` independently and older widget builds keep working.

## What is **not** shared

- Backend API code, database schema, LLM prompts, authentication — all backend-only.
- The widget's TypeScript source — only the compiled `qasper-widget.js` is hosted at `qasper.ai/embed/`.
- React dashboard code, customer data, telemetry.

## When this contract itself changes

Update this table **and** the test assertions on the backend side (`src/lib/embedSnippets.test.ts`) in the same PR. Open paired PRs across both repos and reference them in each other's descriptions.
