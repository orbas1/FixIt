# FixIt Platform Wireframes — Current State

The current state wireframes capture the existing (pre-upgrade) navigation, page layout, and logic pathways for the FixIt web and mobile applications, as well as Firebase integration and security tooling. FixIt is a **services marketplace** that connects households, facilities teams, and vetted service companies (crews with dispatch, equipment, and warranties) — not a freelance gig board. Each flow below maps service-package selection, crew scheduling, and operational controls so that every option leads to a clearly defined UI or data action.

---

## 1. Web Application

### 1.1 Admin Dashboard Panel

```
┌──────────────────────────────────────────────────────────────────────────┐
│ Global Header                                                            │
│ ├─ Logo                                                                  │
│ ├─ Environment Switch (Prod/Sandbox)                                     │
│ ├─ Quick Actions (Create Announcement | Invite Service Company | Run     │
│ │  Service Demand Report)                                                │
│ └─ Profile Menu (My Profile, Notifications, Settings, Sign Out)          │
├──────────────────────────────────────────────────────────────────────────┤
│ Sidebar Navigation                                                       │
│ 1. Overview                                                              │
│ 2. Users                                                                 │
│ 3. Services & Work Orders                                                │
│ 4. Provider Onboarding                                                   │
│ 5. Crew & Equipment Dispatch                                             │
│ 6. Payments & Escrow                                                     │
│ 7. Disputes                                                              │
│ 8. Content Moderation                                                    │
│ 9. Analytics & Reports                                                   │
│10. System Settings                                                       │
│11. Support Inbox                                                         │
├──────────────────────────────────────────────────────────────────────────┤
│ Content Area (Overview)                                                  │
│ ├─ KPI Cards (Active Work Orders, Revenue, Pending Disputes, New Crews)  │
│ ├─ Service Demand Funnel                                                 │
│ ├─ Recent Activity Feed (filters: All/Admin/Automation)                  │
│ └─ Alerts (High priority disputes, warranty claims, payment failures)    │
└──────────────────────────────────────────────────────────────────────────┘
```

**Navigation Flow Summary**

1. **Authentication → Landing**: Admin enters credentials → enforced MFA (email OTP) → role + permission scopes hydrated → redirected to last visited nav item or default Overview.
2. **Global Header Actions**: Quick Actions and Environment Switch are always available; actions open layered modals without leaving current context.
3. **Sidebar Navigation Depth**:
   * Selecting a top-level item expands nested sub-pages (e.g., *Users →* Households, Facilities, Service Operators).
   * Breadcrumbs appear in the content header once a sub-page is selected.
4. **Context Panels**: KPI cards, detail views, and activity feed entries open right-hand slide-out panels when “View Details” is clicked, preserving table scroll position.

**Logic Mapping**

| User Action | Logic / Navigation | Data Dependencies |
|-------------|--------------------|-------------------|
| Select **Users** in sidebar | Loads tab view (Households, Facilities, Service Operators) with table filters. | `/api/users?role=` query; cached lists. |
| Click **Provider Onboarding** | Shows onboarding board (Applied → Compliance Review → Crew Training → Approved). Buttons to approve/deny. | Workflow state machine; triggers compliance checklist tasks + notification when state changes. |
| Open **Crew & Equipment Dispatch** | Displays live map of crews, equipment checkout logs, and incident timeline. | `crew_status` table + telemetry from mobile apps. |
| Use **Quick Action: Run Report** | Opens modal with report types (Service category demand, Revenue, Warranty claims). | Dispatch job to `reports` queue; notify via in-app alerts. |
| Change **Environment** | Toggles between production/sandbox data contexts with confirmation modal. | Switches API base URL and disables write actions in sandbox. |
| Hover alert badge in header | Opens toast with high-priority alerts summary. Clicking entry deep-links to source module. | Alerts API; marks alert as read after view event dispatched. |
| Search within tables | Client-side filter first, fallback to server search when query >3 chars. | `/api/search` endpoint; results paginated. |

**Settings Coverage**

* General: Platform name, logo upload, contact email, default service regions.
* Billing: Default commission %, payout schedule, tax profile, service warranty toggle.
* Integrations: Stripe keys, Pusher channels, Email provider, telematics/fleet trackers, calendar sync.
* Security: Password policy (min length), session timeout, IP allowlist.
* Notifications: Digest schedule, escalation routing list, webhook destinations.
* Operations: Crew roster import, equipment categories, preventative maintenance reminder cadence, partner referral network.
* Data Retention: Archive timelines, export destinations, anonymization triggers.

### 1.2 User Types — Current Web Experience

#### 1.2.1 Customer (Service Requester)

```
Dashboard Tabs: [My Services] [Browse Service Companies] [Messages] [Wallet]
```

| Area | UI Blocks | Option Logic |
|------|-----------|--------------|
| My Services | Summary cards (Active Service Plans, One-off Requests, Needs Attention). Table with filters (Status, Category, Service Address). | Selecting a row opens Work Order detail; status changes (Reschedule, Cancel, Mark Completed) require reason + confirmation modal. |
| Browse Service Companies | Search bar, filters (Category, Rating, Coverage Radius, Emergency Availability). Company cards include CTAs **Request Service Package** and **Book Visit**. | Request package opens modal with service bundle, crew size, required equipment, schedule window validation against provider dispatch calendar. |
| Messages | Two-pane chat (thread list + conversation) with “Share Access” to add household/facility teammates. Attachment upload limited to images/PDF. | New message triggers push + email to service company. Attachments scanned via AV pipeline (basic ClamAV). |
| Wallet | Balance display, service warranty indicator, transaction list, **Add Funds**/**Withdraw** buttons. | Add Funds redirects to payment checkout; Withdraw opens bank details modal (requires verified identity). Warranty badge links to claim form. |

Settings → accessible via profile dropdown: personal info, notification preferences (email, push, SMS), saved service addresses, warranty preferences.

**Supporting Flows & States**

* **Service Booking Wizard**: Accessible via header CTA. Steps — Details → Required Outcomes → Schedule Window → Crew Preferences (crew size, gender preference, PPE requirements) → Confirmation. Validation surfaces required equipment warnings for specialty services.
* **Work Order Detail Screen**: Tabs (Overview, Crew Assignments, Materials, Payments, History). Approving milestones triggers escrow release confirmation and starts warranty timer.
* **Error Handling**: Payment failure banner on Wallet; Retry button calls payment endpoint again and logs attempt. If repeated failure, prompts to switch payment source or contact support.
* **Notifications**: Customers can mute chat threads individually; state stored per thread. Emergency dispatch alerts cannot be muted.

#### 1.2.2 Service Provider (Service Company)

```
Dashboard Sections: [Lead Board] [Work Orders] [Crew Schedule] [Earnings] [Company Profile]
```

| Section | Details | Logic |
|---------|---------|-------|
| Lead Board | Kanban columns (New, Responded, Won, Archived) with badge for recurring service plan opportunities. Actions: Accept, Decline, Message. | Accept converts lead to Work Order template; Decline asks reason; Message opens chat. Recurring plan acceptance generates future visits automatically. |
| Work Orders | List with filters (Status, Service Address, Crew). Buttons: Update Progress, Request Payment, Dispatch Backup Crew. | Update progress updates work order timeline; Request Payment triggers escrow release request; Dispatch opens crew selector constrained by availability + skill tags. |
| Crew Schedule | Month/week view + list of crews. Toggle to view by crew or territory. | Sync toggle opens OAuth connect; events read-only (current). Assigning job to crew checks certifications before confirming. |
| Earnings | Graph, payout list, maintenance fund contributions, download CSV. | Download calls `/api/earnings/export`. Maintenance fund slider writes to provider profile. |
| Company Profile | Service categories, coverage radius map, dispatch zones, equipment inventory uploads, insurance certificates. | Document upload triggers verification state (Pending → Verified) and surfaces renewal reminders. |

Settings: Availability (business hours, blackout dates), Notification channels, Tax info (W-9 upload), Team members (Owner, Dispatcher, Field Tech, Finance), Preferred warranty terms, Referral partnerships.

**Additional Logic**

* **Lead Alerts**: Bell icon shows count; clicking opens modal listing latest service requests with Accept/Decline buttons. Emergency badge appears for rush services.
* **Document Verification**: Upload triggers status card; rejection surfaces reason with “Resubmit” CTA. Includes license type (e.g., HVAC, Electrical, Plumbing).
* **Team Member Roles**: Roles (Owner, Dispatcher, Field Tech, Finance). Invite sends email, requires acceptance before visible in dashboard.
* **Calendar Conflicts**: Overlapping jobs prompt conflict modal (Reassign Crew, Reschedule, Offer Partner Referral). Referral prompts selection of partner company and logs revenue share.
* **Equipment Checkout**: Clicking tool icon opens equipment drawer showing checked-out assets; returning equipment requires condition note.

#### 1.2.3 Support Agent (Internal User)

* Dashboard: Ticket queue segmented by service category and region, canned responses, escalation buttons.
* Logic: Assign to self, change status, escalate to Admin (adds to Admin alerts).
* Settings: Signature management, schedule (for chat hours), macros library with service-category variants.
* Routing: Queue filters (Priority, Channel, Topic, Service Region). Accepting ticket locks it for 10 minutes of inactivity before auto-release.
* Escalation Flow: Requires reason + impacted users; logs to `support_escalations` and notifies admin via alert badge.
* Field Incident Intake: Dedicated tab for on-site incidents submitted by crews, prompting verification photos and safety checklist review.

---

## 2. Phone Application (Flutter)

### 2.1 Admin Mobile App (Current)

```
Login → MFA Prompt → Dashboard Tabs: Overview | Users | Work Orders | Alerts | Settings
```

* **Overview Screen**: KPI cards (Active Work Orders, Crews Dispatched, Outstanding Warranties), scrollable alerts list. Pull-to-refresh.
* **Users Screen**: Filter chips (Role: Household, Facility, Service Company, Crew Member). Swipe actions (Suspend, Reset Password, Switch to Partner Company).
* **Work Orders Screen**: Segmented control (Scheduled, In Progress, Escalated). Selecting work order opens detail with crew roster, materials list, and action buttons.
* **Alerts Screen**: System notifications with severity color coding. “Dispatch Now” CTA available for emergency jobs; acknowledgement toggles status.
* **Settings Screen**: Account, Notifications (push/email toggles), Operations (default service regions, warranty templates), App Preferences (theme, language), Security (biometric login toggle, session timeout slider).

Flow logic: After login, app checks device trust (via `/api/mobile/devices`). If untrusted, prompts for verification code sent via email.

**Mobile-Specific Logic**

* **Deep Links**: Push notification tap routes directly to the relevant tab + work order detail screen.
* **Offline Handling**: Displays banner “Data may be stale”; restricts destructive actions when offline. Shows queue of pending actions for later sync.
* **Settings Sync**: Changes persist via PATCH to `/api/mobile/admin/settings`; local cache updated via hydrated bloc.

### 2.2 Customer Mobile App

* **Home (Feed)**: Search, quick action cards (Book a Service, Browse Packages, Track Work Order). Selecting Book leads to multi-step form (Details → Visit Window → Materials → Review).
* **Work Orders Tab**: Active work orders list; each item has CTAs (Chat, Update, Pay, Extend Warranty). Payment CTA opens embedded webview to checkout.
* **Messages Tab**: Similar to web, with attachment restrictions. Push notifications route to thread.
* **Wallet & Warranty Tab**: Balance, add card, withdraw (disabled until identity verified), view warranty coverage.
* **Profile Tab**: Settings (personal info, service addresses, notifications, household members, account deletion request).

**Detailed Option Mapping**

| Flow | Steps | Edge Cases |
|------|-------|------------|
| Book a Service | Step 1 (Category + Desired Outcome) → Step 2 (Location + Visit Window) → Step 3 (Materials supplied by Customer vs Provider) → Step 4 (Review + Submit). | If visit window not available, show alternatives modal; if materials flagged as hazardous, prompt to confirm storage instructions. |
| Track Work Order | Opens timeline. Buttons: Message Provider, Add Update, Raise Issue, Request Crew Change. | Raise Issue requires selecting type (Delay, Quality, Safety) and uploading evidence. Crew change checks provider availability. |
| Wallet Add Card | Uses Stripe SDK; on failure shows inline error; success shows confirmation sheet and updates list. | Declined card suggests contacting bank; offers fallback to PayPal if enabled. |
| Notifications Settings | Toggles per category (Work order updates, Promotions, Security, Warranty). | Security toggle cannot be disabled; warranty reminders pinned if coverage expiring. |

### 2.3 Provider Mobile App

* **Dashboard**: Summary metrics (Active Service Plans, Crews On-Site), pending leads, quick actions (Update Availability, Respond to Leads, Dispatch Standby Crew).
* **Leads Tab**: Card stack; swipe right accept (with confirm), left decline (choose reason). Emergency leads show countdown badge.
* **Work Orders Tab**: Pipeline view, progress updates (Crew En Route, On Site, Complete) with photo upload. Safety-sensitive categories require two photos (before/after).
* **Calendar Tab**: Scrollable list; tap to block time or assign crew.
* **Inventory Tab**: Track consumables (filters, cleaning supplies) with reorder button; low stock triggers push notification.
* **Earnings Tab**: Weekly summary, payouts, export via email.
* **Settings**: Availability schedule, coverage zones, notification preferences, bank accounts, compliance documents.

**Conditional Logic**

* **Lead Swipe**: Decline triggers reason picker (Busy, Out of Service Scope, Price). If “Price”, prompts to suggest service package alternative or refer partner company.
* **Work Order Progress Updates**: Requires photo when marking “Complete”; failure to upload disables completion. Safety categories prompt additional checklist (gas shutoff, equipment lockout).
* **Calendar Sync**: When toggled on, app requests permission; if denied, shows instructions to enable via OS settings.
* **Inventory Reorder**: When stock level below threshold, prompts to create purchase order and suggests preferred suppliers.
* **Bank Accounts**: Adding account uses Plaid flow; manual entry fallback if Plaid unavailable.

---

## 3. Firebase (Current Wireframe)

```
Firebase Project: fixit-platform
├─ Auth
│  ├─ Providers: Email/Password, Google, Apple
│  ├─ MFA: SMS (via Firebase Phone Auth)
│  └─ Custom Claims: role (admin/provider/customer)
├─ Firestore
│  ├─ collections
│  │   ├─ notifications
│  │   ├─ device_tokens
│  │   ├─ service_areas
│  │   ├─ crew_status
│  │   └─ audit_logs
│  └─ Security Rules: role + service-area scoped read/write
├─ Cloud Messaging
│  └─ Topics: global, job_{id}, crew_{id}, admin_alerts
├─ Storage (limited use)
│  └─ Buckets: uploads/temporary
└─ Functions (minimal)
    ├─ onNotificationCreate → send FCM
    └─ onUserCreate → seed profile defaults
```

**Logic**: Mobile apps fetch device token + service role → store in `device_tokens` with userId + platform. Dispatch managers also register crew assignments to topic subscriptions.

**Indexes & Rules Overview**

* Firestore composite indexes: `notifications` by `(userId, createdAt desc)`; `audit_logs` by `(resourceType, createdAt)`; `crew_status` by `(providerId, availabilityState)`.
* Security rules enforce `role` claim: admins read/write, providers limited to own docs and registered service areas, crews restricted to assigned work orders.
* Storage rules require metadata `scanned=true` before download; achieved via background job.

**Data Flow**

1. Auth success → Cloud Function `onUserCreate` seeds Firestore profile with base role and default service regions.
2. App registers device token + service role → writes to Firestore → triggers Cloud Messaging topic subscription for admin, dispatch, or crew channels.
3. Notifications inserted via backend → `onNotificationCreate` dispatches FCM to appropriate topic or individual token (e.g., `crew_{id}` for field teams).

---

## 4. Security (Current Wireframe)

```
Security Console
├─ Dashboards
│  ├─ Login Attempts (charts)
│  ├─ Suspicious Activity Feed
│  └─ Vulnerability Scan Status
├─ Controls
│  ├─ MFA Enforcement (toggle per role)
│  ├─ Password Policy Editor
│  ├─ IP Allowlist Manager
│  └─ Session & Device Revocation
├─ Incident Response
│  ├─ Playbooks (dropdown)
│  ├─ Runbook Steps
│  └─ Postmortem Templates
└─ Audit Logs
   ├─ Export CSV/JSON
   └─ Integrations (SIEM webhook URL)
```

**Logic Mapping**

* Selecting **Login Attempts** timeframe updates chart + table.
* Toggling **MFA Enforcement** updates policy and triggers notification to impacted users.
* Adding IP to allowlist requires name + CIDR → validation → success toast.
* Incident playbook selection loads checklist; marking steps complete writes to `security_incidents` table.
* Exporting audit logs prompts format selection (CSV, JSON). JSON includes signed checksum.
* SIEM webhook test sends sample payload and shows status badge (Success/Failed).

**Security Settings Inventory**

| Category | Settings | Notes |
|----------|----------|-------|
| Authentication | Password rules, MFA enforcement per role, session timeout, device trust list. | Device trust list shows fingerprint + last seen, includes crew tablets. |
| Access Control | Role definitions (Dispatcher, Field Supervisor, Finance), permission toggles, emergency access mode. | Emergency mode requires two admin approvals. |
| Monitoring | Alert thresholds, webhook endpoints, pager rotation. | Pager rotation integrates with Ops calendar feed and crew escalation tree. |
| Compliance | Data retention timers, export templates, approval routing, service warranty policy archives. | Exports require dual sign-off before download link active. |
| Incident Response | Playbook templates, simulation scheduler, communications matrix. | Simulations auto-create faux incidents for training and tie to specific service categories. |

---

## 5. Settings Matrix (Current)

| Area | Settings | Dependencies |
|------|----------|--------------|
| Notifications | Email, SMS, Push toggles per event (work orders, crew arrival, warranty, promotions). | Requires verified email/phone; push requires device token. |
| Payments | Default payment method, auto-pay toggle, payout accounts, warranty deductible settings. | Stripe customer/account IDs. |
| Privacy | Data download request, delete account, visibility toggles (profile public/private), household sharing. | Triggers compliance job; household sharing invites managed via email confirmation. |
| Localization | Timezone picker, preferred language, measurement units (imperial/metric). | Updates user profile + caches. |
| Support Preferences | Preferred contact channel, support PIN, accessibility requests, emergency contact. | Support PIN required for live agent; stored hashed. |
| Integrations | Connected calendars, smart home connections (smart locks), analytics opt-in, facilities CMMS linkage. | OAuth tokens stored encrypted; revocation available. |

---

These artifacts represent the baseline service-focused experience before the upcoming platform upgrades.
