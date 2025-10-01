# Runbook Library

The runbooks in this folder provide operational guardrails for the FixIt marketplace. They are structured for on-call engineers and release managers, and reference the tooling that ships with this repository (Laravel Artisan commands, Horizon, Telescope, CloudWatch dashboards, and Sentry).

Each runbook contains:

- **Scope & purpose** – when to reach for the runbook and what outcomes it guarantees.
- **Readiness & dependencies** – environment, data, and tooling checks required before executing the procedure.
- **Execution steps** – numbered, time-bounded actions with copy/paste-ready commands.
- **Observability & verification** – metrics, logs, dashboards, and alerts to watch during and after execution.
- **Rollback / contingency** – instructions if verification fails or customer impact continues.
- **Communications** – who to notify and how to document the event in the incident tracker.

> Use the runbooks as version-controlled living documents. After every drill or live event, capture updates in a pull request and link the incident ticket in the document history section of each runbook.

## Index

| Runbook | Primary Audience | Summary |
| --- | --- | --- |
| [Blue/Green Deployment](deployment-blue-green.md) | Release managers, SRE | Safe deployment procedure for Laravel monolith and Flutter web assets with traffic draining, schema migrations, and smoke validation. |
| [Major Incident Response](incident-response.md) | Incident commander, on-call engineer | End-to-end incident management flow, including classification, containment, and customer comms. |
| [Payments & Payouts Recovery](payments-stripe-runbook.md) | FinOps, Payments engineering | Stripe reconciliation, ledger validation, payout freeze/remediation, and fraud response. |
| [Disputes Surge Management](disputes-runbook.md) | Support leads, Trust & Safety | Scaling moderators, adjusting SLA timers, templated communications, and ledger verification. |
| [Post-Incident Review](post-incident-review.md) | Engineering leadership, QA, Compliance | Facilitated review template, corrective action tracking, and evidence archiving. |

## Change management

- Store incident IDs, drill numbers, and reviewer sign-offs in the **Document history** table inside each runbook.
- Version bump runbook docs whenever process or tooling meaningfully changes; reference the commit hash in incident retrospectives.
- Automate guardrails where possible (e.g., Terraform drift detection, Stripe reconciliation jobs) and keep runbooks focused on human decision points.
