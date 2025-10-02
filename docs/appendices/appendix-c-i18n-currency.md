# Appendix C — Internationalization & Currency Governance

## Overview
Internationalization (i18n) and currency localization span both the Laravel platform and the Flutter mobile applications. This appendix codifies ownership, architecture, operational expectations, and automated validation so teams can confidently ship multi-lingual, multi-currency experiences without regressions.

## Ownership & Stakeholders
| Domain | Primary Owner | Supporting Functions | KPIs |
| --- | --- | --- | --- |
| Translation Catalogue (Laravel) | Web Platform Team | Localization Guild, QA | Missing key rate ≤ 0.1%, release audit completeness |
| Translation Catalogue (Flutter) | Mobile Platform Team | Localization Guild | Missing key rate ≤ 0.1%, widget smoke regression pass |
| Currency Rules & Formatting | FinTech Team | Mobile + Web Platform, Accounting | Pricing defect rate = 0, FX update SLA < 4h |
| RTL Accessibility | Accessibility Guild | Mobile Platform, QA | RTL usability score ≥ 90, blocker defects = 0 |

## Architecture Summary
- **Laravel** uses PHP array catalogues under `resources/lang/<locale>` with dot-notation lookup. The base `en` catalogue defines the contract; all locales must match its keyset.
- **Flutter** ships static map-based catalogues in `apps/provider/lib/common/languages/*.dart`. These drive in-app translations via `AppLocalizations`.
- **Currency** metadata is captured via `CurrencyModel` with symbol placement, separators, and precision, now consumed by the shared `CurrencyFormatter` utility to guarantee consistent rendering.
- **Evidence Asset** `apps/provider/assets/config/i18n_currency_checklist.json` records locale metadata, QA samples, and audit timestamps for governance dashboards.

## Translation Guidance (Task 85A)
1. **Glossary & Terminology Alignment**
   - Maintain `glossary` keys across both Laravel and Flutter catalogues to avoid divergent terms.
   - Use sentence case for UI text; reserve ALL CAPS for badges/CTA patterns documented in UI guidelines.
2. **Key Management**
   - Every new string lands in `resources/lang/en` and `apps/provider/lib/common/languages/en.dart` first.
   - Use hierarchical prefixes (`booking.status.accepted`) in Laravel to group semantics; mirror flattened keys in Flutter to minimise drift.
   - Deprecation process: mark keys with `@deprecated` comment and remove after two release trains when consumers are migrated.
3. **RTL Readiness**
   - Guard layout widgets with `Directionality` or `AlignmentDirectional` when text can wrap.
   - Provide mirrored assets (e.g., `assets/svg/us_currency.svg`) flagged with `_rtl` suffix where necessary.
4. **Pluralisation & Formatting**
   - Fallback to Laravel’s built-in pluralisation helpers (`trans_choice`) and Flutter’s `intl` plural APIs. The shared glossary references the plural rules per locale.
5. **Review Cadence**
   - Weekly translation sync with Localization Guild.
   - Release gating: translation completeness ≥ 99.9%, blocker bug count = 0.

## Currency Guidance (Task 85A)
- **Precision**: Persist up to 4 decimals for FX-driven calculations; presentation defaults to `CurrencyModel.noOfDecimal`.
- **Symbol Placement**: Drive formatting via metadata (`left`/`right`). Never concatenate symbols manually.
- **Separators**: Honour locale-defined thousands/decimal separators; do not assume `,` and `.`.
- **Exchange Rates**: `CurrencyProvider.currencyVal` stores the active FX multiplier, refreshed hourly from the pricing service.
- **Compliance**: Currency changes are audit logged with user, timestamp, and delta for SOX parity.

## Automation & QA Evidence (Task 85B)
### Scripts
- `scripts/i18n_currency_doctor.sh` orchestrates the validation suite.
- `scripts/internal_i18n_currency_doctor.py` verifies:
  - Laravel catalogue parity across locales.
  - Flutter catalogue parity and RTL flag accuracy.
  - Currency profile integrity (symbol placement, separators, decimal precision).
  - Formatting samples from `i18n_currency_checklist.json` using the shared formatter logic.
- Script output is machine-readable for CI ingestion (JSON) and human-friendly summaries.

### Mobile Dashboard
The provider app surfaces the checklist under **Environment → Localization & Currency** featuring:
- Translation coverage per locale (Flutter + Laravel).
- Currency formatting verification with expected vs. actual results.
- RTL readiness indicators per locale.
- Timestamped audit metadata from the evidence asset.

### QA Workflow
1. **Automated Checks**: Run `scripts/i18n_currency_doctor.sh` in CI and before release candidates.
2. **Manual Spot Checks**: QA validates RTL navigation flows and currency totals for each supported locale using curated scripts in TestRail.
3. **Regression Assets**: Evidence JSON (`apps/provider/assets/config/i18n_currency_checklist.json`) archived each release with generated timestamp and coverage scores.
4. **Escalation**: Any doctor failure opens a Sev-2 incident with Localization + FinTech leads; release blocked until resolved.

## Maintenance & Continuous Improvement
- Update the evidence asset when locales or currency profiles change; automation enforces schema validity.
- Localization Guild reviews glossary quarterly.
- FinTech Team publishes FX source-of-truth change logs with diffed separators/precision when markets expand.

## References
- `scripts/i18n_currency_doctor.sh`
- `scripts/internal_i18n_currency_doctor.py`
- `apps/provider/assets/config/i18n_currency_checklist.json`
- `apps/provider/lib/utils/currency_formatter.dart`
- `apps/provider/lib/screens/app_pages_screens/environment_checklist_screen/layouts/i18n_currency_section.dart`

