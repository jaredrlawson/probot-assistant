# Changelog

All notable changes to the **ProBot Assistant** plugin will be documented in this file. 
This project adheres to [Semantic Versioning](https://semver.org/).

## [1.6.0-beta.4] — 2026-03-13
### 🎨 UI & Layout Refactor
* **Knowledge Base Sidebar**: Redesigned the Knowledge Base into a two-column layout with a sticky sidebar for settings, quick actions, and export/import tools.
* **Article Writer Refactor**: Modernized the Article Writer with a cleaner grid layout, better input readability, and a dedicated sidebar for billing route control and actions.
* **UI Consistency**: Standardized the use of the `.pbot-settings-layout` grid system across all primary admin views to prevent "stretched" layouts on large screens.
* **Status Card Integration**: Added consistent AI Brain status indicators to the Knowledge Base sidebar.
* **Master Brain Rebranding**: Renamed "Dashboard" to "Master Brain" and "Secretary Master Brain" to "AI Master Brain" for a more focused AI experience.
* **Refined Bubble Icons**: Streamlined bubble icon options in Settings and improved the live design preview bubble.

## [1.6.0-beta.3] — 2026-03-12
### 🔮 HUD & Lead Generation Upgrade
* **Business HUD**: Completely redesigned the Answering Dashboard into a high-value business tracker (Leads, Bookings, Chats, Revenue).
* **Lead Generation**: Integrated new "New Leads" tracking via SMS Opt-ins and "Stay Connected" popup.
* **Live Booking Sync**: Real-time integration with the Alchemy Booking plugin; bookings and revenue now update the HUD instantly.
* **Stay Connected Popup**: Added a Cedar Point-style dark-mode compliance modal for phone lead capture.
* **Status Toggle**: New availability switcher (Online/Busy) that informs the Llama 3.3 70B brain in real-time.
* **Brain Upgrade**: Migrated AI core to **Llama 3.3 70B Versatile** for superior reasoning and style matching.
* **Reactive Status Indicators**: New pulsing UI elements (Green/Red) that sync across the HUD and Command Terminal.
* **UI Customization**: Added "Window Corner Radius" setting for the desktop chat panel.
* **Full-Width Layout**: Expanded admin dashboards to 100% width for edge-to-edge data visibility.
* **Fix**: Enforced compact HTML output and normalized CSS spacing for AI responses to eliminate excessive whitespace.

## [1.6.0-beta.2] — 2026-03-11
### 🛡️ Security & UX Hardening
* **SSRF Prevention**: Secured the Secretary Brain AJAX proxy to use only the configured VPS IP and validated routes.
* **XSS Hardening**: Added robust sanitization for Knowledge Base JSON intents and Secretary chat responses.
* **CSRF Protection**: Implemented nonce verification for all administrative AJAX calls and settings updates.
* **Reactive UI**: Redesigned Admin Settings layout for a cleaner vertical stack.

## [1.6.0-beta.1] — 2026-03-05
### 🚀 The AI Business Assistant Milestone
* **Autopilot Engine**: Introduced background worker using WP-Cron for automated article generation.
* **Topic Queue**: Added a dedicated dashboard to queue multiple topics for future AI generation.
* **AI Auto-Category**: New feature to automatically detect, create, and assign WordPress categories based on AI output.
* **SEO Maximizer**: Integrated advanced system prompting to prioritize keyword density, H2/H3 structure, and meta data.
* **Dual Credit System**: REST API support for `channel=writer|phone` usage incrementing.

## [1.5.8] — 2025-09-01
### Added
- **Product Key Groundwork**: Integrated live validation against the license server.
- **Tier Display**: Added UI support for Free, Starter, and Pro plan badges in Admin.
- **Credit Metering**: Implemented usage deduction for Article Writer previews.
### Changed
- **Mobile UI**: Polished license management by converting tables into responsive cards.

## [1.5.7] — 2025-08-31
### Added
- **Unified Versioning**: New badge system for the Plugins list and Admin header (Stable vs. Beta).
- **Global Constants**: All version labels now pull from a single `PROBOT_VERSION` constant.
### Fixed
- Version parsing bug that caused duplicate "-beta Beta" strings.

## [1.5.6] — 2025-08-24
### Changed
- **Modular Architecture**: Split admin logic into specialized drop-ins (`admin-settings`, `admin-knowledge`, `admin-article-writer`).
- **Admin JS**: Added autosize helpers, color-picker sync, and live slider readouts.

## [1.5.5] — 2025-08-20
### Added
- **Visual Controls**: Native color pickers and sliders for Halo and Pulse intensity.
- **Knowledge Base**: Independent "Set Greeting" block and "Copy JSON" utility.
- **UI Polish**: Hardened the scroll model and fixed keyboard overlap using `visualViewport`.

## [1.5.4] — 2025-08-18
### Added
- **Import/Export**: JSON-based Knowledge Base migration tools.
- **AJAX Actions**: Quick-add Q/A and Greeting updates without page refreshes.
### Fixed
- Toast message input overflowing on mobile screens.

## [1.5.1] - [1.5.3] — 2025-08-10
### Added
- **Fuzzy Matching**: Initial release of Jaccard + Levenshtein fallback logic.
- **Dynamic Typing**: Delay effect that scales based on reply length.
- **Cache-Busting**: Automatic asset versioning via `filemtime()`.

## [1.5.0] — 2025-08-01
### Added
- **Initial Modern Release**: Floating bubble, panel, teaser toast, and admin settings.
- **Mobile-First Overlay**: Responsive design for phones with desktop popup fallback.

## [1.0.0] - [1.4.0] — 2025-06-01
- **Initial Scaffolding**: Basic chat panel, static responses, and vanilla JS architecture.
- **WP Integration**: Standard plugin hooks and options registration.
