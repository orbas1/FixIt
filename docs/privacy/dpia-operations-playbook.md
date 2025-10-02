# DPIA Operations Playbook

## Purpose

The DPIA (Data Protection Impact Assessment) playbook ensures FixIt continuously evaluates privacy risks, documents mitigations, and provides regulator-ready evidence for processing activities across marketplace, payments, messaging, and analytics domains.

## Roles & Responsibilities

| Role | Responsibilities |
| --- | --- |
| Data Protection Officer | Oversees DPIA portfolio, approves assessments, and maintains regulator contact packages. |
| Domain Steward | Registers assets, updates processing purposes, and executes mitigation tasks assigned in DPIA findings. |
| Security Engineering | Provides encryption and logging guardrails, validates residency configurations, and supports remediation. |
| Legal & Compliance | Reviews lawful bases, cross-border safeguards, and ensures contractual clauses remain current. |

## Workflow

1. **Asset Registration**: Steward registers the asset via `/api/v1/governance/data-assets` with lawful bases, residency policies, and retention.
2. **Automated Trigger**: `DpiaAutomationService` calculates risk on registration/update. If risk ≥ threshold or `requires_dpia = true`, a DPIA is created in `in_review` status with seeded findings when controls are missing.
3. **Evidence Gathering**: `DpiaRecord` captures mitigation actions, residual risks, and links to residency policies. Findings capture severity, due dates, and status transitions.
4. **Review & Approval**:
   - Update mitigation/residual details via `PUT /api/v1/governance/dpia-records/{id}`.
   - Reviewers close findings and approve the record. The automation service automatically sets the next review date based on governance policy.
5. **Monitoring**: Weekly scheduler runs `governance:data-doctor` to flag stale records or new risks. Reports stored at `storage/app/data_governance_doctor_report.json` feed dashboards and audits.
6. **Incident Response Alignment**: If an asset enters `non_compliant` status, open a security incident referencing the DPIA record, assign remediation owners, and track to closure.

## Templates & Automation

- **Mitigation Template**: Each finding uses `{ action, owner, due_at, status }`. Automation ensures due dates default to four weeks and statuses transition through `open → in_progress → mitigated`.
- **Residual Risk Template**: `{ risk, status }` with statuses `tracked`, `accepted`, or `transferred`. Updates must include justification for acceptance.
- **Cross-Border Safeguards**: When `cross_border_allowed = true`, policy must define `transfer_safeguards` (SCC, BCR, local equivalents). Automation fails the governance doctor if safeguards are absent.

## Metrics & Reporting

- **Coverage**: Percentage of assets with approved DPIAs vs. `requires_dpia = true`.
- **Latency**: Mean time from automated trigger to DPIA approval.
- **Open Findings**: Count of findings by severity to support prioritization.
- **Residency Drift**: Number of policies referencing inactive zones or unapproved countries.

Metrics are exported through the JSON output of `governance:data-doctor --format=json` for ingestion by BI pipelines.

## Tooling Reference

| Asset | Purpose |
| --- | --- |
| `app/Services/DataGovernance/DpiaAutomationService.php` | Risk scoring, automated DPIA creation, default findings, closure workflows. |
| `app/Http/Controllers/API/DpiaRecordController.php` | REST interface for DPIA CRUD and findings management. |
| `scripts/data_governance_doctor.sh` | CLI pipeline to run migrations in isolation, produce compliance JSON, and validate via Python checks. |
| `config/data_governance.php` | Central definitions for classifications, lawful bases, residency regions, encryption profiles, and DPIA thresholds. |

## Continuous Improvement

- Review risk-scoring weights quarterly with Legal and Security leadership.
- Capture lessons learned from incidents in DPIA residual risk notes.
- Extend automation to integrate vendor assessments and third-party risk dashboards in future iterations.
