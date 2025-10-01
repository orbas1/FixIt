# Major Incident Response Runbook

**Audience:** Incident commander (IC), on-call engineer, communications lead  
**Scope:** Sev-0/Sev-1 incidents impacting availability, security, payments, or compliance obligations.

## 1. Activation criteria

Trigger this runbook when any of the following occur:

- API error rate > 5% for 5 consecutive minutes (New Relic alert NR-API-5XX).
- Checkout, payout, or login failures > 2% (Mixpanel funnel `checkout_failure`, Stripe webhook delivery failures).
- Security event flagged by SIEM (Critical severity) or regulator notification window < 24h.
- Public relations/customer trust risk (social escalation, press inquiry, legal threat).

## 2. Initial response (< 5 minutes)

| Step | Owner | Details |
| --- | --- | --- |
| Page responders | PagerDuty automation | IC, Backend On-Call, Infra On-Call, Communications lead. |
| Confirm channel | IC | Create/confirm Slack channel `#inc-fixit-<date>` + Zoom bridge. |
| Set status | IC | Post initial status: impact summary, start time, roles assigned. |
| Engage playbook | IC | Link this runbook in thread, assign note taker.

## 3. Classification & containment (5–15 minutes)

1. **Triage:**
   - Review alerts (New Relic, Sentry, CloudWatch) and recent deployments (`git log --since="2 hours"`).
   - Validate customer impact via synthetic smoke test (`php artisan smoke:touch --scenario=provider-checkout`).
2. **Contain:**
   - Rollback or disable feature flags causing incident (LaunchDarkly toggles `provider.tax.*`).
   - Scale out failing workers: `php artisan horizon:pause` (pause) then `php artisan horizon:continue` after fix.
   - For DDoS or traffic spikes: enable AWS WAF rule set `fixit-blocklist`.
3. **Security-specific:** rotate credentials (`aws secretsmanager rotate-secret`) if leak suspected.

## 4. Investigation & mitigation (15–60 minutes)

- Assign SMEs for suspected systems (Payments, Search, Mobile) and capture hypotheses in Slack thread.
- Inspect logs via CloudWatch Insights using saved query `incidents/high_error_rate`.
- For database issues, enable Performance Insights and check slow query log (`mysql -e "SHOW FULL PROCESSLIST"`).
- Implement fix via feature flag, config change, or hotfix PR. All code changes require peer review unless emergency (document rationale).

## 5. Communications cadence

| Audience | Cadence | Medium | Owner |
| --- | --- | --- | --- |
| Internal stakeholders | Every 15 min | Slack thread | Communications lead |
| Executive update | Hourly or on demand | Email/Slack DM | IC |
| Customer status page | Initial + resolution | Statuspage.io | Communications lead |
| Regulatory / PCI contact | Within SLA (as needed) | Email/phone | Compliance officer |

Provide template for updates:

```
Impact: <systems/users>
Cause: <suspected>
Actions: <what's being done>
ETA: <time or unknown>
Next update: <timestamp>
```

## 6. Resolution & verification

1. Confirm primary impact resolved: error rate < baseline for 15 min, queue depth stable, zero failing smoke tests.
2. Document root cause and mitigation steps in incident ticket (Jira `INC-XXXX`).
3. Update status page to resolved with summary.
4. Schedule post-incident review within 48 hours (use [post-incident review runbook](post-incident-review.md)).

## 7. Incident timeline template

| Timestamp | Owner | Event |
| --- | --- | --- |
| 00:00 | PagerDuty | Alert triggered (NR-API-5XX) |
| 00:02 | IC | Incident declared, roles assigned |
| 00:05 | Backend On-Call | Feature flag toggled off |
| 00:20 | Payments SME | Hotfix deployed |
| 00:45 | IC | Incident resolved, start recovery |

## 8. Post-incident tasks

- Export Slack channel transcript and attach to incident ticket.
- Ensure all temporary mitigations are reverted (feature flags, rate limits).
- File follow-up engineering tasks with owners, due dates, and success metrics.
- Update relevant runbooks with learnings.

## 9. Document history

| Date | Incident | Change summary | Reviewer |
| --- | --- | --- | --- |
| 2025-09-06 | Stage-31 | Initial runbook committed. | @incident-commander |
