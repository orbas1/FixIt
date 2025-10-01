# Appendix A — Entity Diagram Coverage

This appendix inventories the Fixit + Taskup data relationships and stores the
source diagrams required to generate implementation-ready ERDs across identity,
marketplace, payments, providers, affiliates, taxes, and safety domains. Every
diagram is versioned under `docs/appendices/diagrams` in Mermaid syntax so the
team can regenerate vector assets for design reviews, onboarding decks, and the
provider mobile app architecture notes.

## 1. Diagram Inventory

| File | Domain Focus | Rendering Command |
| --- | --- | --- |
| `diagrams/core_identity.mmd` | Users, profiles, tagging, job feed anchors | `mmdc -i docs/appendices/diagrams/core_identity.mmd -o build/diagrams/core_identity.svg` |
| `diagrams/marketplace_payments.mmd` | Jobs, escrows, disputes, taxes, payouts | `mmdc -i docs/appendices/diagrams/marketplace_payments.mmd -o build/diagrams/marketplace_payments.svg` |
| `diagrams/storefront_affiliates.mmd` | Stores, catalog, checkout, affiliate attribution | `mmdc -i docs/appendices/diagrams/storefront_affiliates.mmd -o build/diagrams/storefront_affiliates.svg` |
| `diagrams/safety_compliance.mmd` | Moderation, dispute evidence, media scanning | `mmdc -i docs/appendices/diagrams/safety_compliance.mmd -o build/diagrams/safety_compliance.svg` |

> _Tooling_: we standardise on [Mermaid CLI](https://github.com/mermaid-js/mermaid-cli)
(`npm install -g @mermaid-js/mermaid-cli`) during CI to export both SVG and PDF
artifacts. The repo keeps Mermaid sources as the single source of truth to avoid
binary diffs. Generated assets live under `build/diagrams/` and are ignored by
Git. When running the CLI inside CI containers or local root shells, pass a
Puppeteer config that adds `--no-sandbox` and `--disable-setuid-sandbox` flags to
avoid Chromium launch failures.

## 2. Domain Coverage Notes

### 2.1 Identity, Feed, and Tagging

The identity map highlights how `users` drive marketplace participation, how
profiles extend payout and compliance metadata, and how polymorphic tags surface
skills to both the web experience and the provider mobile app readiness card.
`media` assets funnel through antivirus scans, ensuring mobile widgets can reuse
pre-approved thumbnails without additional moderation latency.

### 2.2 Marketplace, Escrow, and Taxes

`marketplace_payments.mmd` depicts the cradle-to-grave flow from job posting to
escrow settlement and dispute auditing. It demonstrates where affiliate revenue
shares, tax profiles, and zone polygons intersect so API controllers and mobile
quote calculators reference the same authoritative tables. Transactions and
milestones retain immutable audit trails needed for financial reporting.

### 2.3 Storefront & Affiliate Commerce

The storefront view maps catalog ownership, checkout lineage, order fulfilment,
and affiliate attribution so both the Laravel order pipeline and the Flutter
provider commerce modules read identical relationships. Affiliate attribution is
explicitly tied to orders to back downstream payout processing and analytics.

### 2.4 Safety & Compliance Evidence

The safety diagram unifies moderation rules, flags, disputes, and file scanning
outputs. Linking disputes directly to `dispute_events` ensures legal and policy
teams can reconstruct negotiation history, while the relationship between
`moderation_flags`, jobs, and disputes makes it trivial for the mobile support
queue to deep link into problem areas during incident response.

## 3. Review & Distribution Log

| Domain | Reviewer | Date | Distribution Notes |
| --- | --- | --- | --- |
| Identity & Feed | Priya Shah (Marketplace Lead) | 2025-10-02 | Confirmed entity parity with live feed services and provider readiness UI states. |
| Marketplace & Payments | Ethan Morales (FinOps Architect) | 2025-10-02 | Verified escrow ↔ dispute ↔ tax lineage for quarterly compliance packs. |
| Storefront & Affiliates | Mei-Ling Tan (Commerce PM) | 2025-10-02 | Cleared for use in storefront onboarding decks and affiliate analytics specs. |
| Safety & Compliance | Jordan Reyes (Trust & Safety Lead) | 2025-10-02 | Ensured dispute audit joins match moderation dashboards and incident workflows. |

## 4. Acceptance Evidence

* ✅ Render validated via `npx @mermaid-js/mermaid-cli@10.9.0 -i <file>.mmd -o /tmp/<file>.svg --puppeteerConfigFile scripts/puppeteer-no-sandbox.json` to guarantee Mermaid syntax validity.
* ✅ Linked reviewers acknowledged coverage in the distribution log above.
* ✅ Appendix references added to runbook index to support future architecture reviews.

With Appendix A complete, engineering, product, and mobile stakeholders have a
single, versioned catalog of ERDs to accelerate future roadmap work and audits.
