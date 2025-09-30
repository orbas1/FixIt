# Security Incident Response Runbook

This runbook codifies the FixIt production security incident lifecycle introduced in 7.9–7.10. It is designed for 24/7 security operations and aligns with SOC 2 / ISO 27001 expectations.

## 1. Detection & Intake

1. **Trigger sources**
   - Automated signals (WAF, IDS/IPS, SIEM correlation rules, CloudTrail, GuardDuty, Laravel audit log anomalies).
   - Customer or partner reports via abuse@ mailbox, in-product reporting, or trust & safety queue.
   - Bug bounty submissions through HackerOne or equivalent vendor feeds.
2. **Immediate actions**
   - Open `/api/v1/security/incidents` (via the admin console) and submit the initial report using severity guidance below.
   - Capture impacted asset identifiers (hosts, services, database clusters, queue workers) and attach any IOC hash lists.
   - Notify #security-incidents Slack channel with permalink to the incident detail page.

### Severity matrix

| Severity | Definition | Time to acknowledge | Time to containment |
|----------|------------|---------------------|---------------------|
| **Critical** | Active exploitation impacting production data, or regulatory breach in progress. | ≤ 5 minutes | ≤ 30 minutes |
| **High** | Privilege escalation, lateral movement detected, or broad service degradation. | ≤ 15 minutes | ≤ 2 hours |
| **Medium** | Contained vulnerability or misconfiguration with limited blast radius. | ≤ 1 hour | ≤ 8 hours |
| **Low** | False positive validation, partner inquiry, or informational issues. | ≤ 4 hours | ≤ 24 hours |

## 2. Triage & Containment

1. **Acknowledge** the incident via `POST /api/v1/security/incidents/{id}/acknowledge` with the on-call engineer’s note.
2. **Contain**
   - Apply WAF blocks, rotate impacted credentials, disable compromised accounts, or isolate infrastructure nodes.
   - Document every containment action inside the incident timeline for audit.
3. **Communicate**
   - Update status page (if customer-facing degradation), inform leadership bridge, and record stakeholder decisions in the follow-up actions array.

## 3. Eradication & Recovery

1. Execute mitigation steps defined in the runbook updates (patching, redeploying hardened builds, restoring from clean backups).
2. Validate recovery through smoke tests, automated acceptance suites, and monitoring baselines.
3. Record `root_cause`, `impact_summary`, and `mitigation_steps` via `POST /api/v1/security/incidents/{id}/resolve`.

## 4. Post-Incident Review

1. Schedule the postmortem within 48 hours. Use timeline exports from the API response for precise sequencing.
2. Populate `follow_up_actions` with owners, due dates, and validation criteria. These feed directly into the compliance dashboard.
3. Once remediation is verified and all follow-ups are on track, execute `POST /api/v1/security/incidents/{id}/close` with the executive approval note.
4. Update this runbook and related playbooks with lessons learned. Submit a pull request referencing `runbook_updates` stored in the incident.

## 5. Metrics & Reporting

- **MTTA** (Mean Time to Acknowledge) and **MTTR** (Mean Time to Resolve) are derived from the API payloads and exported nightly to the analytics warehouse.
- Maintain MTTA ≤ 10 minutes for critical incidents and MTTR ≤ 4 hours.
- Weekly governance review audits:
  - Timeline completeness (no gaps > 15 minutes during active response).
  - Evidence attachments stored in S3 incident bucket (`s3://fixit-security-incidents/{public_id}/`).
  - Checklist adherence (communications, customer notifications, regulator filings).

## 6. Tooling Integrations

- The Incident Response API emits notifications (email + database) to all `admin` role holders. Extend watchers by assigning the `admin` role to security duty managers.
- Add PagerDuty webhook (via notification channel) to ensure redundant alerting for critical incidents.
- Integrate with SIEM by pushing incident JSON to the `security_incidents` index for correlation.

## 7. Compliance Hooks

- Each closed incident must include:
  - Root cause narrative covering people, process, and technology.
  - Link to postmortem document in Notion/Confluence.
  - Signed approval (note field) from VP Security or delegate.
- Quarterly audits export runbook updates to the compliance binder.

Keep this runbook version controlled. Changes require approval from the Security Steering Committee and must be accompanied by updated automated acceptance tests.
