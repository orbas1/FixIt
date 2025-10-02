# Appendix D — Enterprise Architecture Governance & Roadmap Operations

This appendix codifies the structures, cadences, and evidence expectations that power the FixIt architecture review board (ARB) and capability roadmap office. It is the authoritative reference for chartering decisions, cross-domain dependency management, and long-range planning artifacts. The automation in `scripts/architecture_governance_doctor.sh` validates this appendix alongside the mobile governance dashboard asset `apps/provider/assets/config/architecture_governance_checklist.json`.

## 1. Charter & RACI Matrix

| Function | Accountable | Responsible | Consulted | Informed |
| --- | --- | --- | --- | --- |
| Architecture Review Board (ARB) | Chief Architect | Domain Architects, Platform Leads | Security, Compliance, Product, Finance | Engineering Guilds, PMO |
| Capability Roadmap Office (CRO) | VP, Platform Strategy | Enterprise PMO | Domain Architects, Finance, GTM | Executive Staff, Customer Advisory Board |
| Decision Record Stewardship | Staff Architect, Governance | Domain Leads | QA, Security, Support | Engineering, Product |
| Dependency Heatmap Maintenance | Enterprise PMO | Delivery Managers | Domain Architects | CRO, ARB |

Key expectations:

* The ARB is authoritative on cross-domain architecture principles, exceptions, and reference architectures.
* The CRO operates the capability roadmap backlog, ensuring dependency transparency and business alignment.
* Decision records (ADR series) must be filed within 48 hours of an approved decision and linked to associated capabilities and roadmap milestones.

## 2. Cadence & Intake Model

The ARB meets twice monthly with supplemental emergency reviews as required. Each session is structured as follows:

1. **Portfolio health review (15 minutes)** — lead indicators, roadmap variance, and risk register updates.
2. **Decision requests (45 minutes)** — presenters must supply a lightweight ADR draft, capability impact statement, dependency graph, and security/privacy assessment.
3. **Action ratification (15 minutes)** — confirm decision ownership, due dates, and evidence expectations.
4. **Backlog grooming (15 minutes)** — CRO and PMO review new capabilities and adjust sequencing.

Intake checklist:

* Submit the `ARB-INTAKE` template via the portfolio tool at least four business days before the session.
* Provide architecture diagrams, risk assessment, capacity forecast, data residency implications, and cost envelope.
* Define measurable success criteria and telemetry signals, with owners for each metric.

## 3. Decision Records & Evidence

Decision quality is enforced via the following evidence expectations:

* Every ADR entry must capture context, decision, status (proposed, accepted, superseded), decision date, impact radius, and enforcement tier.
* Evidence attachments include review recordings, security/privacy signoffs, and testing/rollout playbooks.
* The governance script validates that no accepted ADRs are missing enforcement owners or renewal dates.

Evidence storage lives under `storage/app/governance/` partitioned by fiscal quarter. Access is governed by the compliance and security teams, with read replicas for auditors.

## 4. Capability Roadmap & Dependency Operations

The roadmap is organized into quarterly capability increments. Each capability profile contains:

* `id` — globally unique slug aligning with Jira Epics and architecture documentation.
* `title` and `summary` — concise articulation of the business outcome and technical scope.
* `owners` — accountable leaders spanning product, engineering, and operations.
* `roadmap_quarter` — quarter in ISO format (e.g., `2024-Q3`).
* `dependencies` — upstream capability IDs. Every dependency must exist in the same dataset.
* `readiness` — rolling status including architecture review outcome, security posture, operational readiness, and release gating.
* `metrics` — measurable KPIs with baselines and targets.

Dependencies are visualized in the capability heatmap, synchronized nightly with the CRO workspace. Owners are required to update readiness signals weekly; the automation raises alerts if data is stale beyond seven days.

## 5. Mobile Governance Dashboard

The provider mobile app surfaces a governance dashboard under the environment readiness section. It displays:

* ARB charter summary and next review session.
* Active decision records requiring follow-up actions.
* Capability roadmap, grouped by quarter with dependency badge counts.
* Risk indicators when cadence or evidence expectations are missed.

The dashboard consumes `architecture_governance_checklist.json`. Providers must review upcoming governance impacts before deploying changes or requesting infrastructure variances.

## 6. Automation & Quality Gates

`scripts/architecture_governance_doctor.sh` orchestrates validation by invoking `internal_architecture_governance_doctor.py`. The checks enforce:

* Presence of all required sections in this appendix.
* Structural integrity of the governance checklist asset (board membership, cadences, capability dependencies, decision evidence, and metric coverage).
* Freshness of roadmap readiness timestamps.
* Alignment between capability dependencies and defined capabilities.
* No overdue renewal or review dates for ARB charter, decision records, or roadmap cadences.

### Running the Doctor Locally

```bash
./scripts/architecture_governance_doctor.sh --mode=local
```

The command emits structured JSON to `storage/logs/architecture-governance-report.json` for CI ingestion. Failures block deployment pipelines until remediated.

## 7. Continuous Improvement

* Annual charter retrospectives revalidate ARB mandate, participant roster, and tooling effectiveness.
* Quarterly maturity assessments score governance health across decision throughput, roadmap predictability, and dependency lead time.
* Lessons learned feed into Appendix A diagrams, Appendix B secrets operations, and Appendix C i18n currency governance to preserve cross-domain alignment.

The ARB chair is accountable for maintaining this appendix. Updates must be reviewed through the governance checklist doctor to guarantee structural compliance and evidence continuity.
