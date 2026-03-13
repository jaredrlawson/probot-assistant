=== ProBot Assistant ===
Contributors: Jared Я Lawson
Tags: chatbot, assistant, AI, customer support, virtual receptionist, WordPress AI
Requires at least: 5.5
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.6.0-beta.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ProBot Assistant is an **AI-powered business assistant** for WordPress.  
It combines a floating chat bubble, knowledge base, AI article writer, and now **Business HUD with live booking sync** — helping businesses scale leads and revenue.

== Description ==

ProBot isn’t just another chatbot. It’s designed as an **AI-powered assistant** for business owners:

- 🟢 **Business HUD**: Real-time tracking for Leads, Bookings, Chats, and Revenue.
- 🟢 **Live Booking Sync**: Seamless integration with the Alchemy Booking plugin.
- 🟢 **Lead Generation**: "Stay Connected" compliance modal for SMS opt-ins.
- 🟢 **Status Toggle**: Real-time switcher (Online/Busy) that informs the AI brain.
- 🟢 **Brain Upgrade**: Powered by **Llama 3.3 70B Versatile** for deep reasoning.
- 🟢 Floating chat bubble with **pulse halo** + teaser toast  
- 🟢 JSON-driven knowledge base with Import/Export  
- 🟢 Manual Q/A editor in WP Admin  
- 🟢 Fuzzy matching + adjustable threshold  
- 🟢 **Mobile-first fullscreen** (phones), **popup** (desktop)  
- 🟢 Color customization via pickers or CSS vars  
- 🟢 Built-in GitHub self-updater  
- 🟢 **License + Product Key system** (paid tiers unlock AI features)  
- 🟢 Dual credits: **Writer Credits** + **Phone Credits**  

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

== Changelog ==

= 1.6.0-beta.3 — 2026-03-12 =
* 🔮 **HUD & Lead Generation Upgrade**
* **Business HUD**: Completely redesigned the Answering Dashboard into a high-value business tracker.
* **Lead Generation**: Integrated new "New Leads" tracking via SMS Opt-ins and "Stay Connected" popup.
* **Live Booking Sync**: Real-time integration with the Alchemy Booking plugin.
* **Status Toggle**: New availability switcher (Online/Busy) that informs the AI brain.
* **Brain Upgrade**: Migrated AI core to **Llama 3.3 70B Versatile**.
* **UI Customization**: Added "Window Corner Radius" setting for the desktop chat panel.
* **Fix**: Enforced compact HTML output and normalized CSS spacing for AI responses.

= 1.6.0-beta.2 — 2026-03-11 =
* 🛡️ **Security & UX Hardening**
* SSRF Prevention, XSS Hardening, and CSRF Protection.
* Redesigned Admin Settings layout for a cleaner vertical stack.

= 1.6.0-beta.1 — 2026-03-05 =  
* 🚀 Major new milestone: **paid feature line begins**  
* Added **license + product key system** (integrates with Square)  
* Dual credit buckets: **writer credits** + **phone credits**  
* Article Writer: expanded beta groundwork  
* AI Answering: initial beta scaffolding (call capture, transcription, credits)  

= 1.5.8 (Stable) — 2025-09-01 =  
* Added **Product Key integration groundwork**  
* Mobile UI for license management polished (cards instead of table)  

= 1.5.7 (Stable) — 2025-08-31 =  
* Introduced **unified version badge system**.
* All version labels now pull from a single **PROBOT_VERSION** constant.

== Roadmap ==

= 1.6.0 (AI Business Assistant) =  
- Full Article Writer rollout (multi-tier credits, categories, scheduling)  
- AI Answering (call capture + message forwarding)  
- Lead gen handoff (email/CRM)  
- Upsell system (Upgrade → Plans in WP Admin)  
- Live Chat operator patch-through  
- Email campaigns (MailChimp/SendGrid integrations)  
- Security scanner (AI-driven)  
