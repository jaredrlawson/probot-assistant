=== ProBot Assistant ===
Contributors: Jared Я Lawson
Tags: chatbot, assistant, AI, customer support, fuzzy match, WordPress assistant
Requires at least: 5.5
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.5.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ProBot Assistant is a front-end chat assistant for WordPress. It supports JSON-driven responses, fuzzy matching, teaser toast prompts, and mobile-first UX. Built for speed, flexibility, and monetization.

== Description ==

ProBot Assistant lets you add an interactive chat bubble to your site with:
- 100% JSON-driven intents (manual or packaged)
- Fuzzy matching of visitor input
- Configurable greeting delays and teaser "toast" messages (duration & show count)
- Mobile-first responsive design with keyboard/viewport safety
- Admin interface for settings & knowledge base management (Quick Add, Set Greeting)
- Color customization with native pickers + text inputs
- Halo & pulse intensity controls
- Built-in updater ready

Perfect for customer support, FAQs, lead gen, and beyond.

== Installation ==

1. Upload the `probot-assistant` folder to `/wp-content/plugins/`
2. Activate the plugin in WordPress → Plugins
3. Configure via **ProBot Assistant** in the WP Admin menu

== Frequently Asked Questions ==

= Where do I put my intents? =
Go to **ProBot Assistant → Knowledge Base**. Choose **Packaged** (ships with the plugin) or **Manual** (paste/edit JSON). You can import/export JSON from there.

= Does it need an API key? =
No for the base plugin. Option fields exist for future add-ons (e.g., OpenAI fallback) but they’re optional.

= Can I customize colors? =
Yes. Use the native color pickers or paste values (hex/RGB/RGBA). You can also tweak advanced CSS variables if you want deeper styling.

== Changelog ==

= 1.5.5 (Stable) =
* **Stable milestone** 🎉
* Native **color pickers** + text inputs for Bubble / Halo / Window.
* **Halo intensity** and **Pulse intensity** sliders.
* Tight, right-aligned numeric inputs for: fuzzy match, duration, show count, typing delay.
* Knowledge Base: **Set Greeting** as its own block; **Copy JSON** button; raw JSON now in a scrollable area.
* Admin notice banner: **“Settings saved.”**
* Teaser toast positioning & desktop side fixes retained.
* General polish and minor bug fixes.

= 1.5.4 =
* Patch: Fixed admin **toast message input** overflowing off-screen on mobile.
* Improved responsive CSS for settings panel.
* Minor desktop design tweaks.
* Groundwork for configurable toast frequency & custom text.

= 1.5.3 =
* Folder structure cleanup (`/frontend`, `/admin`, `/json`).
* Chat bubble icon hover/tap fix (no blue flash).
* Send button redesign (rounded square, subtle push/hover).
* Fixed “double-tap to send” on mobile.
* Typing delay tuning: scales with reply length.
* Close now fully resets chat session.

= 1.5.2 =
* Added `greeting_delay_ms` (minimum dots before greeting appears).
* Improved fuzzy matching + JSON intent handling.

= 1.5.1 =
* Initial fuzzy match release.
* JSON-driven intents (manual or packaged).
* Basic admin settings + knowledge base UI.

= 1.5.0 =
* Initial modern release: chat bubble, panel, teaser toast, admin pages.

= 1.0.0 → 1.4.x (historical) =
<!-- TODO: Paste your original 1.0.0–1.4.x notes here so we can merge them verbatim. -->

== Roadmap ==

= 1.5.x (Polish series) =
Ongoing polish and UX tightening across 1.5.6 → 1.5.9 as needed to keep the free branch rock-solid.

= 1.6.0 (Paid features begin) =
- Article Writer (full): monthly/weekly/bi-weekly cadence locks by tier, title generation, 800–1000 words, optional AI category
- License/Product key & OpenAI key integration (paid upgrades)
- Additional pro UI refinements and update/notice improvements

== Upgrade Notice ==

= 1.5.5 =
Stable milestone. Color pickers, halo/pulse intensity, KB improvements, and admin “Settings saved” banner.

= 1.5.4 =
Fixes toast input overflow on mobile and minor design issues.