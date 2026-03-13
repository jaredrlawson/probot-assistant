# ProBot Assistant

Front-end chat assistant with teaser, JSON intents, fuzzy matching, and deep site context.

## Changelog

### 1.6.0-beta.3 — 2026-03-12
* **🔮 The Alchemist HUD & Lead Generation Upgrade**
* **Alchemist Business HUD:** Redesigned Answering Dashboard into a high-value business tracker (Leads, Bookings, Chats, Revenue).
* **Lead Generation:** Integrated new "New Leads" tracking via SMS Opt-ins.
* **Live Booking Sync:** Real-time integration with the Alchemy Booking plugin for instant revenue tracking.
* **Stay Connected Popup:** Cedar Point-style dark-mode compliance modal for phone lead capture.
* **Alchemist Status Toggle:** Real-time availability switcher (Online/Busy) that informs the AI brain.
* **Brain Upgrade:** Migrated AI core to **Llama 3.3 70B Versatile** for superior Reasoning.
* **Reactive UI:** Pulse-synced status indicators and full-width edge-to-edge dashboard layout.
* **UI Customization:** Added "Window Corner Radius" setting for the desktop chat panel.

### 1.6.0-beta.2 — 2026-03-11
* **Security Patch (High Priority)**
* **SSRF Prevention:** Secured the Secretary Brain AJAX proxy to use only the configured VPS IP and validated routes.
* **XSS Hardening:** Added robust sanitization for Knowledge Base JSON intents (input and output) and Secretary chat responses.
* **CSRF Protection:** Implemented nonce verification for all administrative AJAX calls and settings updates.
* **Sanitization Fixes:** Preserved functional HTML in Article Writer notices while preventing reflected XSS via `wp_kses`.
* **UX Improvement:** Redesigned Admin Settings layout for a cleaner vertical stack (labels above inputs).
* **Fix:** Resolved "double ding" on Safari by ensuring audio only plays when the chat is visible.
* **Fix:** Resolved scrolling issues in Firefox via standard CSS flexbox layout.

### 1.6.0-beta.1 — 2025-09-03
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

### 1.5.8 (Stable) — 2025-09-01
* Added **Product Key integration groundwork**
    - Keys now validated against license server
    - Free vs Starter vs Pro tiers displayed in admin
    - Usage credits deducted for Article Writer previews
* Fixed teaser mobile positioning.
* Added "Alchemical" theme accents.
