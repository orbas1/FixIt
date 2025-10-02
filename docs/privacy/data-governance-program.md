# Enterprise Data Governance, Residency, and Privacy Program

## Overview

The data governance program codifies how FixIt inventories sensitive data assets, classifies information, enforces residency obligations, and automates privacy-impact assessments. The platform now provides:

- **Data Asset Registry** powered by Laravel models (`DataAsset`, `DataDomain`, `DataResidencyPolicy`, `DataResidencyZone`, `DpiaRecord`, `DpiaFinding`).
- **Governance APIs** under `/api/v1/governance` that enable classification, stewardship assignment, residency policy management, and DPIA orchestration.
- **Automation Services** (`DataGovernanceRegistry`, `DpiaAutomationService`) that evaluate compliance, synchronize residency policies, and initiate DPIA workflows whenever controls drift.
- **Operational Tooling** via `php artisan governance:data-doctor` and `scripts/data_governance_doctor.sh` to continuously audit compliance posture.
- **Mobile telemetry** through the Flutter compliance provider which surfaces residency and DPIA status to stewards for rapid remediation.

## Data Classification & Residency Framework

### Domains and Assets

1. **Domains** encapsulate high-level data landscapes (identity, marketplace, payments, safety). Each domain maintains an ULID, slug, description, and category tags for searchability.
2. **Assets** store immutable keys, user-friendly names, classification tiers (`public`, `internal`, `confidential`, `restricted`), lawful bases, data elements, retention days, and residency controls. Stewardship links assets to accountable users.
3. **Residency Policies** define lawful bases, encryption profiles, controllers, cross-border allowances, and safeguards per residency zone.

### Residency Zones

- Residency zones track risk ratings, allowed country codes, default controllers, and service whitelists.
- Regions map to policy bundles in `config/data_governance.php`, enabling deterministic validation and re-use across APIs and automation.
- Policies enforce uniqueness per asset/zone/storage role (`primary`, `replica`, `processing`) guaranteeing explicit documentation of replication paths.

### Compliance Evaluation

`DataGovernanceRegistry::evaluateCompliance()` inspects residency policies, verifies DPIA coverage, validates zone activation, and identifies cross-border gaps. Results are cached for 15 minutes to minimize load while keeping near-real-time visibility.

Common evaluation flags include:

- Zones disabled but still referenced by policies.
- Countries missing from approved residency policies.
- Cross-border transfers lacking safeguards.
- Overdue asset reviews.
- Missing DPIA coverage for assets flagged as high risk.

## Privacy Operations & DPIA Automation

### DPIA Lifecycle

- **Automation Trigger**: `DpiaAutomationService::ensureCoverage()` calculates a risk score (classification, data elements, retention, cross-border usage, DPIA requirement) and creates/updates DPIA records.
- **Risk Scoring**: Weighted heuristics cap at 100. Assets marked `requires_dpia` receive bonus risk to guarantee review.
- **Default Findings**: Missing encryption or monitoring controls automatically raise findings with due dates and remediation recommendations.
- **Review Workflow**: Approvers close records via APIs or the `governance:data-doctor` command, which also schedules weekly checks.

### Evidence & Reporting

The `governance:data-doctor` command (and corresponding script) emits machine-readable JSON that includes:

- Total asset coverage.
- Non-compliant and attention-required counts.
- Per-asset compliance context and remediation issues.

`internal_data_governance_doctor.py` persists evidence to `storage/app/data_governance_doctor_report.json` and fails CI when residual risk violates policy (e.g., missing DPIA records for high-risk assets).

## API Contracts

| Endpoint | Method | Purpose |
| --- | --- | --- |
| `/api/v1/governance/data-residency-zones` | GET/POST/PUT/DELETE | Manage residency zoning, controller metadata, and risk levels. |
| `/api/v1/governance/data-assets` | CRUD | Register assets, define data elements, tie stewardship, and attach residency policies. |
| `/api/v1/governance/data-assets/{asset}/dpia/ensure` | POST | Force-run DPIA automation with optional mitigation/residual updates. |
| `/api/v1/governance/dpia-records` | CRUD | Inspect or update DPIA records, including findings and risk scores. |

All routes enforce Sanctum auth, zero-trust middleware, FormRequest validation, and policy checks ensuring only governance roles or stewards operate on assets they own.

## Operational Runbooks

1. **Inventory**: Use the API or web admin to register new assets before launch. Provide lawful bases, retention, and residency policies for each storage role.
2. **Policy Drift Detection**: Schedule `scripts/data_governance_doctor.sh` (already part of weekly automation) to monitor and persist JSON evidence. Integrate outputs with governance dashboards.
3. **DPIA Review**: When `attention_required` or `non_compliant` statuses arise, trigger remediation tasks and update findings through the API to close gaps.
4. **Residency Updates**: For new jurisdictions, update `data_residency_zones` and attach policies; compliance evaluation surfaces issues until remedied.

## Security & Audit

- Policies rely on `RoleEnum::ADMIN` or the `backend.compliance.data_governance.manage` permission; stewards can only access assets they own.
- All database tables implement soft deletes for traceability.
- Command output is persisted to storage for audit trails and appended to governance evidence packages.
- Weekly scheduler automatically runs the doctor to produce time-stamped snapshots, ensuring audit readiness.

## Mobile Integration

Flutter providers consume the governance APIs to surface asset compliance status in the mobile dashboard. Users see residency zones, risk scores, findings, and mitigation tasks, enabling asynchronous remediation while maintaining parity with web operations.
