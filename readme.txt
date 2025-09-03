=== ProBot Assistant ===
Contributors: Jared Я Lawson
Tags: chatbot, assistant, AI, customer support, virtual receptionist, WordPress AI
Requires at least: 5.5
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.5.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ProBot Assistant is an **AI-powered business assistant** for WordPress.  
It combines a floating chat bubble, knowledge base, AI article writer, and now **phone answering credits** — helping businesses cut costs and scale support.

== Description ==

ProBot isn’t just another chatbot. It’s designed as an **AI-powered assistant** for business owners:

- 🟢 Floating chat bubble with **pulse halo** + teaser toast  
- 🟢 JSON-driven knowledge base with Import/Export  
- 🟢 Manual Q/A editor in WP Admin  
- 🟢 Fuzzy matching + adjustable threshold  
- 🟢 Greeting with typing delay effect  
- 🟢 Sound notifications (toggleable)  
- 🟢 **Mobile-first fullscreen** (phones), **popup** (desktop)  
- 🟢 Color customization via pickers or CSS vars  
- 🟢 Halo & pulse intensity sliders  
- 🟢 Built-in GitHub self-updater  
- 🟢 **License + Product Key system** (paid tiers unlock AI features)  
- 🟢 Dual credits: **Writer Credits** + **Phone Credits**  
- 🟢 Article Writer (beta): generates posts from prompts  
- 🟢 AI Answering (beta): voice gateway integration for call capture & handoff  

Businesses can use ProBot as:  
- Customer support chatbot  
- FAQ + knowledge base  
- AI receptionist (call capture, message forwarding)  
- Content writer (SEO articles, blog posts)  
- Lead generator (chat → CRM handoff)  

== Installation ==

1. Upload the `probot-assistant` folder to `/wp-content/plugins/`
2. Activate the plugin in WordPress → Plugins
3. Configure via **ProBot Assistant** in WP Admin
4. (Optional) Enter your **Product Key** to unlock AI features

== Frequently Asked Questions ==

= Do I need an API key? =  
No for the free core features. Paid plans use a **Product Key** to unlock credits (writer + phone). API integration (e.g., OpenAI, telephony provider) is handled for you.

= Can ProBot really answer calls? =  
Yes — via the integrated voice gateway. Free version shows the UI; phone answering requires a paid key with phone credits.

= Is this just a chatbot? =  
No. ProBot is positioned as an **AI-powered business assistant** — answering, writing, and supporting leads.

== Changelog ==

= 1.6.0-beta.1 — 2025-09-03 =  
* 🚀 Major new milestone: **paid feature line begins**  
* Added **license + product key system** (integrates with Square)  
* Dual credit buckets: **writer credits** + **phone credits**  
* REST API now supports `channel=writer|phone` for usage increment  
* Admin UI:  
  - Create Keys: writer + phone limits  
  - Existing Keys: desktop table + mobile cards with dual metrics  
* Article Writer: expanded beta groundwork  
* AI Answering: initial beta scaffolding (call capture, transcription, credits)  
* This is the first **Beta release** of 1.6.0 (not yet RC/stable)  

= 1.5.8 (Stable) — 2025-09-01 =  
* Added **Product Key integration groundwork**  
  - Keys now validated against license server  
  - Free vs Starter vs Pro tiers displayed in admin  
  - Usage credits deducted for Article Writer previews  
* Mobile UI for license management polished (cards instead of table)  
* Preserves backward compatibility for all free features  

= 1.5.7 (Stable) — 2025-08-31 =  
* Introduced **unified version badge system**:  
  - Plugins list shows “Version: 1.5.7 | By Jared Я Lawson | [Stable/Beta]” with colored badge.  
  - Admin settings page header shows `v1.5.x` in green (Stable) or `v1.5.x Beta` in orange (Beta).  
* Cleaner version parsing (no more duplicate “-beta Beta”).  
* All version labels now pull from a single **PROBOT_VERSION** constant for consistency.  
* Verified stable release of updater + admin styling.  

= 1.5.6 (Stable) — 2025-08-24 =  
* Split admin into drop-ins:  
  - `admin-settings-register.php` + `admin-settings-page.php`  
  - `admin-knowledge-register.php` + `admin-knowledge-page.php`  
  - `admin-article-writer.php` (preview scaffold)  
* Admin JS: autosize helpers, color-picker sync, live slider readouts.  

= 1.5.5 (Stable) — 2025-08-20 =  
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

= 1.5.4 — 2025-08-18 =  
* Knowledge Base Import/Export JSON.  
* Wrapped raw JSON viewer + Copy JSON button.  
* Quick Add Q/A and Set Greeting (AJAX).  
* Intents preview dropdown.  
* Patch: Fixed admin **toast message input** overflowing off-screen on mobile.  
* Improved responsive CSS for settings panel.  
* Minor desktop design tweaks.  
* Groundwork for configurable toast frequency & custom text.  

= 1.5.3 — 2025-08-15 =  
* Folder structure cleanup (`/frontend`, `/admin`, `/json`).  
* Chat bubble icon hover/tap fix (no blue flash).  
* Send button redesign (rounded square, subtle push/hover).  
* Fixed “double-tap to send” on mobile.  
* Typing delay tuning: scales with reply length.  
* Close now fully resets chat session.  
* Cache-busting for front-end assets via `filemtime()`.  
* Admin notices for settings/responses saved.  

= 1.5.2 — 2025-08-12 =  
* Added `greeting_delay_ms` (minimum dots before greeting appears).  
* Improved fuzzy matching + JSON intent handling.  
* Teaser placement variables (`--teaser-gap-x`, `--teaser-gap-y`) in CSS.  
* Toast duration and show-count options.  

= 1.5.1 — 2025-08-10 =  
* Initial fuzzy match release.  
* JSON-driven intents (manual or packaged).  
* Basic admin settings + knowledge base UI.  
* Fuzzy matching fallback (Jaccard + Levenshtein).  
* Greeting typing delay setting.  

= 1.5.0 — 2025-08-01 =  
* Initial modern release: chat bubble, panel, teaser toast, admin pages.  
* Packaged JSON (`assets/json/intents.json`).  
* Mobile-first overlay; desktop popup.  
* Admin Settings screen (brand, halo, panel, sound, teaser).  

= 1.4.0 — 2025-07-20 =  
* Modernized chat bubble & icon buttons.  
* Added teaser toast default copy.  

= 1.3.0 — 2025-07-10 =  
* More reliable scroll-to-bottom handling.  
* ARIA labels for accessibility.  

= 1.2.0 — 2025-07-01 =  
* Better mobile keyboard handling.  
* Fixed chat input clipping.  

= 1.1.0 — 2025-06-20 =  
* Admin options for brand title, bubble side, and toggles.  
* Typing dots animation.  

= 1.0.0 — 2025-06-01 =  
* Floating bubble + chat panel.  
* Static responses.  
* Minimal CSS + vanilla JS.  
* WordPress plugin scaffolding.  

== Roadmap ==

= 1.6.0 (AI Business Assistant) =  
- Full Article Writer rollout (multi-tier credits, categories, scheduling)  
- AI Answering (call capture + message forwarding)  
- Lead gen handoff (email/CRM)  
- Upsell system (Upgrade → Plans in WP Admin)  
- Live Chat operator patch-through  
- Email campaigns (MailChimp/SendGrid integrations)  
- Security scanner (AI-driven)  

= 1.5.x (Legacy free polish) =  
Polish-only branch for the free chatbot core.  

== Upgrade Notice ==

= 1.6.0-beta.1 =  
First **Beta release** of the AI-powered assistant line. Adds license/product key, dual credits (writer + phone), Article Writer/AI Answering groundwork.  

= 1.5.8 (Stable) =  
Product Key groundwork for writer credits. Lays foundation for paid features.