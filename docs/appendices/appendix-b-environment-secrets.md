# Appendix B — Environment & Secrets Checklist

FixIt operates across multiple cloud regions, customer tenancies, and mobile delivery channels. Appendix B is the canonical
inventory for runtime namespaces, credential ownership, validation automation, and evidence capture that proves our
configuration is production-ready. The checklist combines Laravel, supporting services, and the Flutter provider app so
compliance and operations teams can certify any release in minutes.

## 1. Environment Namespace Catalogue

| Namespace | Tier | Regions | Primary Owner | Escalation | Delegates | Description |
| --- | --- | --- | --- | --- | --- | --- |
| `prod` | Tier 0 | `us-east-1`, `eu-central-1` | Platform SRE | PagerDuty: fixit-prod | Compliance Lead, Mobile Lead | Live customer traffic, card processing, payout execution. Requires 24/7 coverage and SOC2 change controls. |
| `stg` | Tier 1 | `us-east-1` | Release Engineering | Slack: #re-staging | QA Manager, Mobile QA | Canary for release validation, contract testing, and mobile regression packs. Mirrors production IAM but scoped secrets. |
| `qa` | Tier 2 | `us-west-2` | Quality Engineering | PagerDuty: fixit-qa | Automation Lead | End-to-end automated flows, chaos engineering rehearsal, and dispute SLA drills. |
| `dev` | Tier 3 | `local`, `docker` | Developer Experience | Slack: #dx-help | Security Champion, Mobile Platform | Local Docker Compose stacks and ephemeral preview apps. Secrets brokered through Vault dev mounts. |
| `support` | Tier 2 | `us-east-1` | Customer Support Ops | PagerDuty: fixit-support | Trust & Safety | Serves CRM/ITSM integrations, support queue automations, and dispute chat mirroring. |
| `mobile` | Tier 1 | `global` | Mobile Platform | PagerDuty: fixit-mobile | Android Lead, iOS Lead | Firebase projects, push credentials, and feature flag pipelines consumed by the Flutter provider app. |

**Namespace controls**

* Minimum guardrails: Terraform state is encrypted via customer-managed keys, IAM enforced by Okta + AWS SSO with just-in-time
  access, and GitOps pull requests require security review for Tier 0/1.
* Observability: every namespace ships metrics to Grafana Cloud, logs to Datadog, and alerts to PagerDuty schedules defined
  above. Alert routing honours quiet hours with escalation to the delegates listed.

## 2. Secret Inventory & Rotation Matrix

| Secret ID | Env Key / Store | Source of Truth | Rotation Cadence | Last Rotated | Next Due | Owner | Dependent Systems |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `prod_app_key` | `APP_KEY` / AWS Secrets Manager `fixit/prod/app-key` | Secrets Manager → SSM Parameter sync | 45 days | 2025-09-01 | 2025-10-16 | Platform Security | Laravel API, Horizon, Octane |
| `prod_db_password` | Aurora cluster `fixit-prod` | AWS Secrets Manager rotation lambda | 30 days | 2025-09-20 | 2025-10-20 | Database Engineering | API, reporting ETL |
| `prod_pusher_key` | `PUSHER_APP_KEY` / HashiCorp Vault prod mount | Manual with Just-In-Time tokens | 60 days | 2025-08-28 | 2025-10-27 | Messaging Guild | Real-time websockets, mobile push fan-out |
| `stg_app_key` | `APP_KEY` / AWS Secrets Manager `fixit/stg/app-key` | Secrets Manager scheduled rotation | 30 days | 2025-09-25 | 2025-10-25 | Release Engineering | Staging API, contract tests |
| `qa_shippo_token` | Vault `secret/qa/shippo` | Vault Rotating Secrets Engine | 7 days | 2025-10-02 | 2025-10-09 | Logistics Integrations | Order fulfilment smoke tests |
| `support_zendesk_oauth` | Azure Key Vault `fixit-support/zendesk` | Azure Automation Runbook | 90 days | 2025-08-15 | 2025-11-13 | Support Ops | ITSM automation, dispute escalations |
| `mobile_firebase_key` | Firebase Admin key (Flutter) | Google Secret Manager `projects/fixit-mobile/secrets/firebase-admin` | 60 days | 2025-09-05 | 2025-11-04 | Mobile Platform | Push, analytics, Remote Config |
| `mobile_sentry_dsn` | Remote config `SENTRY_DSN` | GitOps encrypted manifest | 30 days | 2025-09-28 | 2025-10-28 | Mobile Platform | Flutter provider diagnostics |

*Secrets flagged as `Tier 0` follow four-eyes rotation, with change tickets auto-generated in ServiceNow and audit trails stored in
`storage/logs/secret-rotation.log`.*

## 3. Ownership, RACI & Evidence

| Activity | Responsible | Accountable | Consulted | Informed | Evidence |
| --- | --- | --- | --- | --- | --- |
| Namespace creation/update | Platform SRE | CTO | Security, Compliance | Product, Support | Terraform plans in Atlantis + signed CAB notes |
| Secret rotation Tier 0 | Platform Security | CISO | Product, Finance | Support, QA | Runbook entry + `scripts/env_secrets_doctor.sh` artifact |
| Secret rotation Tier 1-3 | Namespace Owner | Platform Security | Security Champion | All delegates | Rotation automation job logs + GitOps PR |
| Flutter credential sync | Mobile Platform | VP of Mobile | Release Engineering | Support, QA | `EnvironmentChecklistProvider` audit state + Firebase rotation receipts |

Evidence (rotation logs, CI artefacts, screenshots) lives in `storage/app/compliance/environment/` with retention of 18 months.

## 4. Validation Tooling & Automation

### 4.1 Env & Secret Doctor (Laravel + Flutter)

`scripts/env_secrets_doctor.sh` validates critical env keys across `.env`, `.env.example`, and Flutter environment mirrors. It
verifies the presence of required keys, blocks default placeholders (`secret`, `changeme`, `Your ApiUrl`), and checks the
Flutter provider app for production-ready base URLs. The script exports a JSON report to `storage/logs/env-doctor-report.json`
for audit evidence and returns a non-zero exit code if any Tier 0/1 secret is missing or stale.

### 4.2 Runtime Checks

* **Laravel** — `php artisan config:cache` and `php artisan secrets:doctor` (wrapper around the script) run on CI merge.
* **Flutter Provider App** — `EnvironmentChecklistProvider` consumes `assets/config/environment_checklist.json` to surface
  namespace & secret health directly in the mobile UI so providers can confirm compliance before accepting new jobs.
* **Observability** — Honeycomb traces include `x-secret-version` metadata for API bootstrap calls, while Grafana dashboards
  track rotation freshness per namespace.

## 5. Review & Evidence Log

| Date | Reviewed By | Scope | Outcome |
| --- | --- | --- | --- |
| 2025-10-03 | Priya Shah (Platform SRE) | Prod & staging namespaces, APP_KEY rotation | ✅ All rotations within SLA, env doctor passed with no warnings. |
| 2025-10-03 | Malik Ortega (Mobile Platform) | Firebase admin key, Sentry DSN | ✅ Keys rotated, Flutter dashboard refreshed with zero overdue secrets. |
| 2025-10-03 | Jordan Reyes (Trust & Safety) | Support namespace & dispute chat secrets | ✅ Zendesk OAuth rotation validated, evidence archived in compliance vault. |

Appendix B is referenced by runbooks in `docs/runbooks/` and the enterprise acceptance service to guarantee environment and
secret readiness for every release train.
