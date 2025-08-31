=== ProBot Assistant ===
Contributors: Jared Я Lawson
Tags: chatbot, assistant, AI, customer support, fuzzy match, WordPress assistant
Requires at least: 5.5
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.5.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ProBot Assistant is a lightweight, customizable **WordPress chatbot plugin**.  
It gives your site a floating chat bubble, teaser toast, JSON-driven knowledge base,  
and admin tools to manage responses — all mobile-first and responsive.

== Description ==

ProBot Assistant lets you add an interactive chat bubble to your site with:

- 🟢 Floating chat bubble (left/right) with **pulse halo** and teaser toast
- 🟢 JSON-driven knowledge base (packaged or manual upload)
- 🟢 Manual Q/A editor with Import/Export in WP Admin
- 🟢 Fuzzy matching (adjustable threshold)
- 🟢 Greeting with typing delay effect
- 🟢 Sound notifications (toggleable)
- 🟢 **Mobile-first fullscreen** on phones, **desktop popup** on larger screens
- 🟢 Color customization (bubble, halo, panel, toast) via pickers or CSS vars
- 🟢 Halo & pulse intensity sliders
- 🟢 Built-in GitHub self-updater (release-based)
- 🟢 Article Writer **preview** (1.6.0 full rollout planned)

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

= 1.5.7 (2025-08-31) =
* **Admin polish:**
  - Bubble position wrapped in bordered block with inline radios
  - Matching (fuzzy threshold) wrapped in bordered block with cleaner label
  - Greeting typing delay wrapped in bordered block with proper spacing
  - Fixed helper text spacing (e.g., "0 = very loose") to match other controls
* **Sound fixes:**
  - Greeting plays one ding after reopen/minimize
  - Responses now play exactly one ding (no double sounds)
  - Sound suppressed correctly when closed/minimized
* **Stable milestone** — all admin + frontend polish from 1.5.6 now fully stabilized.

= 1.5.6-dev-stable (2025-08-24) =
* Split admin into drop-ins:
  - `admin-settings-register.php` + `admin-settings-page.php`
  - `admin-knowledge-register.php` + `admin-knowledge-page.php`
  - `admin-article-writer.php` (preview scaffold)
* Admin JS: autosize helpers, color-picker sync, live slider readouts.

= 1.5.5 (2025-08-20) =
* **Stable milestone** 🎉
* Native **color pickers** + text inputs for Bubble / Halo / Window.
* **Halo intensity** and **Pulse intensity** sliders.
* Tight, right-aligned numeric inputs for: fuzzy match, duration, show count, typing delay.
* Knowledge Base: **Set Greeting** as its own block; **Copy JSON** button; raw JSON now in a scrollable area.
* Admin notice banner: **“Settings saved.”**
* Teaser toast positioning & desktop side fixes retained.
* General polish and minor bug fixes.
* Stronger halo pulse with soft glow.
* Keyboard overlap fixes with `visualViewport` + CSS vars.
* Non-linear halo intensity mapping for smoother feel.
* Hardened scroll model (only message list scrolls).

= 1.5.4 (2025-08-18) =
* Knowledge Base Import/Export JSON.
* Wrapped raw JSON viewer + Copy JSON button.
* Quick Add Q/A and Set Greeting (AJAX).
* Intents preview dropdown.
* Patch: Fixed admin **toast message input** overflowing off-screen on mobile.
* Improved responsive CSS for settings panel.
* Minor desktop design tweaks.
* Groundwork for configurable toast frequency & custom text.

= 1.5.3 (2025-08-15) =
* Folder structure cleanup (`/frontend`, `/admin`, `/json`).
* Chat bubble icon hover/tap fix (no blue flash).
* Send button redesign (rounded square, subtle push/hover).
* Fixed “double-tap to send” on mobile.
* Typing delay tuning: scales with reply length.
* Close now fully resets chat session.
* Cache-busting for front-end assets via `filemtime()`.
* Admin notices for settings/responses saved.

= 1.5.2 (2025-08-12) =
* Added `greeting_delay_ms` (minimum dots before greeting appears).
* Improved fuzzy matching + JSON intent handling.
* Teaser placement variables (`--teaser-gap-x`, `--teaser-gap-y`) in CSS.
* Toast duration and show-count options.

= 1.5.1 (2025-08-10) =
* Initial fuzzy match release.
* JSON-driven intents (manual or packaged).
* Basic admin settings + knowledge base UI.
* Fuzzy matching fallback (Jaccard + Levenshtein).
* Greeting typing delay setting.

= 1.5.0 (2025-08-01) =
* Initial modern release: chat bubble, panel, teaser toast, admin pages.
* Packaged JSON (`assets/json/intents.json`).
* Mobile-first overlay; desktop popup.
* Admin Settings screen (brand, halo, panel, sound, teaser).

== Roadmap ==

= 1.5.x (Polish series) =
Ongoing polish and UX tightening across 1.5.6 → 1.5.9 as needed to keep the free branch rock-solid.

= 1.6.0 (Paid features begin) =
- Article Writer (full): monthly/weekly/bi-weekly cadence locks by tier, title generation, 800–1000 words, optional AI category
- License/Product key & OpenAI key integration (paid upgrades)
- Additional pro UI refinements and update/notice improvements
- Planned add-ons: lead capture, Square payments, dark mode

== Upgrade Notice ==

= 1.5.7 =
Stabilizes all frontend and admin polish. Fixes double dings, greeting/reply sound logic, and adds bordered blocks for key settings.

= 1.5.6-dev-stable =
Splits admin into modular drop-ins for easier maintenance.

= 1.5.5 =
Stable milestone. Color pickers, halo/pulse intensity, KB improvements, and admin “Settings saved” banner.

= 1.5.4 =
Fixes toast input overflow on mobile and adds KB Import/Export.