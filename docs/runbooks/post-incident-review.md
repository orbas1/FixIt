# Post-Incident Review Runbook

**Audience:** Engineering leadership, QA, Compliance, Product  
**Scope:** Facilitate a structured review within 48 hours of resolving a Sev-0/Sev-1 incident.

## 1. Scheduling & preparation

| Task | Owner | Deadline |
| --- | --- | --- |
| Assign facilitator (not IC) | Director of Engineering | Within 12 hours of incident closure |
| Collect artifacts (logs, dashboards, PRs) | Facilitator | 24 hours |
| Prepare invite list | Facilitator | 24 hours |
| Draft agenda & circulate | Facilitator | 24 hours |

Artifacts checklist:

- Incident ticket (Jira `INC-XXXX`) with timeline and chat transcript.
- Monitoring snapshots (New Relic, Grafana, CloudWatch) at peak and resolution.
- Code diffs, feature flag changes, config updates.
- Customer communications (status page posts, emails).

## 2. Meeting structure (60 minutes)

1. **Welcome & objectives (5 min)** – Set ground rules (blameless, action-oriented).
2. **Recap (10 min)** – IC walks through incident summary and timeline.
3. **What went well (10 min)** – Identify resilience strengths.
4. **What went wrong (20 min)** – Discuss detection gaps, process breakdowns, tooling limits.
5. **Action items (10 min)** – Assign SMART actions with owners and due dates.
6. **Risk review (5 min)** – Compliance & security flag follow-ups.

Use collaborative doc (Notion template `Postmortem v3`) during meeting.

## 3. Action item tracking

- Record actions in Jira under incident epic with labels `postmortem`, `stage31`.
- Each action must include success metric and verification method.
- Add reminder automation (Slack workflow) for due date minus 2 days.

## 4. Publishing & communication

1. Publish postmortem within 48 hours in `Confluence > Reliability > Incidents`.
2. Share summary in `#fixit-status` and CC executive stakeholders.
3. For customer-impacting incidents, send follow-up emails using template `docs/support/templates/post_incident_email.md`.
4. If regulatory reporting required, ensure Compliance files updates in regulator portal.

## 5. Continuous improvement hooks

- Feed improvements into reliability roadmap (`docs/performance/reliability-roadmap.md`).
- Update relevant runbooks (deployment, incident, payments, disputes) with new steps.
- Schedule chaos or game-day exercises based on uncovered failure modes.

## 6. Document history

| Date | Incident | Change summary | Reviewer |
| --- | --- | --- | --- |
| 2025-09-06 | Stage-31 | Initial post-incident review template. | @cto |
