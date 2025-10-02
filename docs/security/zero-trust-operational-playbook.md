# Zero-Trust Operational Playbook

## Daily Controls

1. **Evidence Snapshot** – `php artisan security:zero-trust:evidence` runs at 02:30 UTC. Verify job completion in Horizon/queue logs and archive artefacts to the GRC SharePoint library.
2. **Access Event Review** – Security analysts review `GET /api/v1/security/zero-trust-events?decision=deny` for the previous 24 hours. Create incidents for anomalous signals (e.g., `impossible_travel_detected`).
3. **Trusted Device Hygiene** – Endpoint security reviews `GET /api/v1/security/trusted-devices?trust_level=revoked` to ensure retired devices are quarantined within 24 hours.

## Weekly Controls

* **Network Zone Validation** – Run regression tests against `network_zones` CIDR coverage ensuring corporate VPN ranges are current. Update records through `PUT /api/v1/security/network-zones/{id}`.
* **Policy Calibration** – Analyze evidence snapshots to tune `zero_trust.risk` thresholds. Adjust configuration via `config/zero_trust.php` with CAB approval.
* **Mobile Alignment** – Validate Flutter smoke tests for MFA and zero-trust flows (`flutter test test/security/zero_trust_flow_test.dart`).

## Incident Response Workflow

1. **Detect** – Middleware returns RFC7807 payload with signal detail. SOC alert triggers via SIEM (future integration) or manual review.
2. **Contain** – Revoke associated `trusted_devices` (`DELETE /api/v1/security/trusted-devices/{id}`) and rotate secrets through `security:audits` automation.
3. **Eradicate** – Require MFA re-enrollment for affected identity, adjust `network_zones` if new malicious CIDR discovered.
4. **Recover** – Approve new devices post-verification, confirm zero-trust decisions return to `allow`.
5. **Lessons Learned** – Update this playbook and the zero-trust reference architecture with post-incident notes.

## Quality Gates

| Dimension | Gate | Automation |
| --- | --- | --- |
| Functionality | Feature tests cover login, MFA, device revocation, evidence snapshot. | `tests/Feature/Security/ZeroTrustEvaluatorTest.php` |
| Integration | API contracts validated via Flutter integration harness. | `apps/user/lib/providers/auth_providers` |
| UX | Flutter displays challenge flows with localized messaging. | `apps/user/lib/screens/auth_screens/mfa_verification_screen/` |
| Security | Zero-trust evaluator denies stale MFA, blocked networks, and high-risk signals. | `config/zero_trust.php`, middleware |

## Reporting

* Weekly zero-trust scorecards summarizing events, signals, and outstanding remediation tasks.
* Monthly compliance exports for SOC2/ISO audit evidence referencing snapshot artefacts.
* Executive dashboards (PowerBI/Tableau) ingest JSON evidence to visualize risk trends.

## Contacts

| Role | Team | Responsibilities | Escalation |
| --- | --- | --- | --- |
| Identity Ops Lead | Security Engineering | MFA enforcement, enrollment campaigns | PagerDuty: `sec-ident-1` |
| Network Security Manager | Infrastructure | Network zone curation, VPN access | PagerDuty: `net-sec-1` |
| GRC Program Manager | Risk & Compliance | Evidence retention, audit readiness | Email: `grc@fixit.example` |
