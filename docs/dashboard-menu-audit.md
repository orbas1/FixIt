# Dashboard, Menu, and Styling Inventory

This report lists the current implementation of the requested menus, dashboards, and related assets alongside the gaps compared to the requirements captured in `AGENTS.md` and other project conventions.

## 1. Landing Top Menu
- **Current**
  - Desktop navigation exposes Home, Categories, Services, Service Packages, optional Booking (only for authenticated users), and Blog links, with cart, currency, language, and profile/login controls in the right rail.【F:resources/views/frontend/layout/header.blade.php†L195-L420】
  - Location selector modal and quick currency/language dropdowns are wired into the header to update session state when choices change.【F:resources/views/frontend/layout/header.blade.php†L151-L371】【F:resources/views/frontend/layout/header.blade.php†L430-L459】
- **Missing vs. guidance**
  - AGENTS.md calls for public routes such as `/landing`, `/discover`, and `/product/:id`; the web header does not surface navigation to a discover/search experience or product detail entry points, so pre-login exploration remains hidden.【F:resources/views/frontend/layout/header.blade.php†L195-L234】【F:AGENTS.md†L541-L604】
  - Landing requirements include hero CTAs, discover strips, and deep-link handling instrumentation that is not reflected in the header controls (no CTA analytics hooks or deep-link prompts).【F:AGENTS.md†L553-L556】

## 2. User Dashboard Menu & Settings (by user type)
- **Current**
  - The front-end account sidebar provides tabs for Dashboard, Custom Jobs (feature-flagged), Notifications, Wallet, Saved Addresses, My Reviews, and Logout, with edit-profile modal access from the avatar block for all authenticated consumers.【F:resources/views/frontend/account/sidebar.blade.php†L45-L155】
- **Missing vs. guidance**
  - AGENTS.md expects consumer dashboards to surface active jobs, messaging, upcoming bookings, disputes, and receipts; the menu lacks dedicated entries for conversations, dispute status, or receipt history, limiting parity with the required experience.【F:resources/views/frontend/account/sidebar.blade.php†L45-L122】【F:AGENTS.md†L675-L677】
  - There are no differentiated menus for provider or serviceman personas on the front-end dashboard—only conditional rendering of the Custom Jobs tab—so role-specific settings and widgets mandated by AGENTS.md remain absent outside the admin back office.【F:resources/views/frontend/account/sidebar.blade.php†L45-L122】【F:AGENTS.md†L675-L677】

## 3. User Dashboard (consumer web)
- **Current**
  - The profile dashboard highlights wallet balance, pending/completed service counts, and static profile metadata with edit links, aligning with the sidebar navigation to wallet and booking pages.【F:resources/views/frontend/account/profile.blade.php†L19-L147】
- **Missing vs. guidance**
  - Required widgets such as recent messages, dispute timelines, receipts, and modular server-driven panels are not implemented, leaving the dashboard short of the comprehensive overview described in AGENTS.md.【F:resources/views/frontend/account/profile.blade.php†L19-L147】【F:AGENTS.md†L675-L678】

## 4. Admin Dashboard Menu & Settings
- **Current**
  - The Laravel admin sidebar groups dashboard access, user management (system users, customers, providers, servicemen, unverified lists), service management (zones, services, packages, requests), and booking management with status-filtered submenus.【F:resources/views/backend/layouts/partials/sidebar.blade.php†L31-L420】
  - Role-based `@can` gating tailors visibility for administrators, providers, and servicemen, showing wallet, withdrawal, and location tools when permissions allow.【F:resources/views/backend/layouts/partials/sidebar.blade.php†L41-L249】【F:resources/views/backend/layouts/partials/sidebar.blade.php†L268-L420】
- **Missing vs. guidance**
  - AGENTS.md directs the admin UI to include quick filters, bulk actions, audit trails, impersonation, and mobile-friendly read-only summaries; the current menu only exposes standard CRUD links without these orchestration and oversight controls.【F:resources/views/backend/layouts/partials/sidebar.blade.php†L31-L420】【F:AGENTS.md†L679-L682】
  - Advertising, affiliates, and analytics modules referenced in AGENTS.md lack dedicated navigation entries, indicating those oversight areas are not yet surfaced in the admin shell.【F:resources/views/backend/layouts/partials/sidebar.blade.php†L31-L420】【F:AGENTS.md†L608-L611】【F:AGENTS.md†L720-L732】

## 5. Admin Dashboard (widgets)
- **Current**
  - The dashboard view renders date-range filters plus cards for servicemen, providers, withdraws, and other KPI counts, along with an admin welcome tile.【F:resources/views/backend/dashboard/index.blade.php†L18-L199】
- **Missing vs. guidance**
  - Consumer/provider dashboards are expected to display leads, earnings, conversion funnels, and modular widgets; the existing KPI list omits these advanced charts and lacks dispute, storefront, or affiliate summaries.【F:resources/views/backend/dashboard/index.blade.php†L18-L199】【F:AGENTS.md†L675-L678】
  - Accessibility requirements such as WCAG-compliant contrast and widget modularity (server-driven layouts) are not evident within the static Blade markup.【F:resources/views/backend/dashboard/index.blade.php†L18-L199】【F:AGENTS.md†L683-L689】

## 6. Phone App Menu (consumer app)
- **Current**
  - The user Flutter app defines a bottom navigation menu with Home, Booking, Offer, and Profile tabs, plus guest profile actions like About App and Become Provider prompts.【F:apps/user/lib/common/app_array.dart†L33-L78】
- **Missing vs. guidance**
  - Mobile navigation should include deep links to feed, disputes, checkout, and affiliate areas per the routing contract; these tabs or feature flags are not present in the bottom menu configuration.【F:apps/user/lib/common/app_array.dart†L33-L78】【F:AGENTS.md†L541-L619】

## 7. Phone App User Dashboard (consumer app)
- **Current**
  - `HomeScreen` orchestrates multiple themed layouts (Tokyo, New York, Toronto, Berlin, Dubai) with skeleton loaders, refresh control, and location-aware app bars, covering dashboards, blogs, and services when data is loaded.【F:apps/user/lib/screens/bottom_screens/home_screen/home_screen.dart†L14-L127】
- **Missing vs. guidance**
  - Feed requirements such as filter chip rows, map toggles, offline cache banners, job activity tabs, and analytics instrumentation are absent from the current home/dashboard logic, leaving gaps in discovery and telemetry.【F:apps/user/lib/screens/bottom_screens/home_screen/home_screen.dart†L14-L127】【F:AGENTS.md†L558-L576】

## 8. Profile Dashboard (consumer web)
- **Current**
  - Profile details expose editable name/email/phone/address fields with validation, password change trigger, and illustrative imagery within the dashboard content area.【F:resources/views/frontend/account/profile.blade.php†L83-L188】
- **Missing vs. guidance**
  - Security guidance demands MFA setup, verified contact indicators, and legal/data export controls in profile settings; these account safeguards are not surfaced in the dashboard UI.【F:resources/views/frontend/account/profile.blade.php†L83-L188】【F:AGENTS.md†L614-L619】

## 9. Phone App Screens
### Consumer App
- **Current**
  - The export lists cover app settings, language selection, profile details, wallet balance, favourites, locations, reviews, app details, contact us, help/support, notifications, search, categories, service details, provider details, booking flows (slot booking, coupons, payment), serviceman selection, chat, packages, and booking status views.【F:apps/user/lib/screens/app_pages_screens/app_pages_screen_list.dart†L1-L118】
- **Missing vs. guidance**
  - Screens for disputes, escrow timelines, affiliate dashboards, storefront orders, and advanced search filters (e.g., H3 distance sliders, map views) outlined in AGENTS.md are not represented in the current exports.【F:apps/user/lib/screens/app_pages_screens/app_pages_screen_list.dart†L1-L118】【F:AGENTS.md†L585-L611】

### Provider App
- **Current**
  - Provider exports cover earnings, notifications, service management, serviceman CRUD, blog details, password/language settings, company profile, time slots, packages, commission history, booking lifecycle (pending/accept/assign/ongoing/hold/completed/cancelled), chat, payment, and location screens.【F:apps/provider/lib/screens/app_pages_screens/other_pages_list.dart†L1-L171】
- **Missing vs. guidance**
  - Required modules such as lead funnels, affiliate management, dispute handling, payouts dashboards, and guardrails for `/app/*` routing (with auth/KYC checks) are missing from the provider app screen set.【F:apps/provider/lib/screens/app_pages_screens/other_pages_list.dart†L1-L171】【F:AGENTS.md†L541-L619】【F:AGENTS.md†L675-L678】

## 10. Page Builder Artifacts
- **Current**
  - The backend page builder supports locale-specific editing, app type selection (User vs. Provider), CKEditor content, media uploads for app icons, and SEO meta fields (title, description).【F:resources/views/backend/page/fields.blade.php†L1-L198】
- **Missing vs. guidance**
  - AGENTS.md’s modular widget vision (server-driven layouts and configurable dashboards) is not represented—there are no schema builders or slot registries to drive dynamic landing/dashboard composition.【F:resources/views/backend/page/fields.blade.php†L1-L198】【F:AGENTS.md†L675-L678】

## 11. Styling Descriptions
- **Current**
  - Global CSS defines a primary theme color (`#5465FF`), button radii (6px and 12px for `.btn`/`.btn-solid`), neutral card styling with 10px radius and border, and typography scales (responsive `h1`/`h2`, base paragraphs) using DM Sans and Helvetica fallbacks.【F:public/frontend/css/style.css†L1-L1】
- **Missing vs. guidance**
  - Design tokens for typography scale (Poppins/Inter), spacing system, elevation tokens, motion guidelines, and shared component variants (cards, inputs, modals) mandated in AGENTS.md section 5 are not codified in the current stylesheet or token files.【F:public/frontend/css/style.css†L1-L1】【F:AGENTS.md†L654-L668】

## 12. Fonts
- **Current**
  - Web CSS references the `DM Sans` family with fallbacks to Helvetica/Arial.【F:public/frontend/css/style.css†L1-L1】
  - The consumer Flutter app depends on `google_fonts` but does not declare custom font assets; the provider app bundles only a `downIcon.ttf` asset for icons via its fonts section.【F:apps/user/pubspec.yaml†L38-L180】【F:apps/provider/pubspec.yaml†L40-L160】
- **Missing vs. guidance**
  - AGENTS.md prescribes Poppins or Inter typography tokens across platforms; neither web nor mobile projects load these families as first-class fonts today.【F:public/frontend/css/style.css†L1-L1】【F:apps/user/pubspec.yaml†L38-L180】【F:apps/provider/pubspec.yaml†L40-L160】【F:AGENTS.md†L654-L658】
