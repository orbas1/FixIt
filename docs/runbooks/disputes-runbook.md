# Disputes Surge Management Runbook

**Audience:** Support leads, Trust & Safety, Finance back office  
**Scope:** When dispute volume exceeds staffing capacity or threatens SLA breaches.

## 1. Surge triggers

- > 25 open disputes per moderator or SLA timer within 12 hours of breach.
- Automated alert `disputes_sla_breach` fired (Grafana panel monitoring `disputes.deadline_delta` > 0).
- External escalation (regulator, enterprise customer) requesting expedited handling.

## 2. Preparation checklist

| Check | Owner | Evidence |
| --- | --- | --- |
| Support staffing schedule updated | Support lead | Assembled roster in Ops Planner |
| Template responses current | Trust & Safety | `docs/support/templates/disputes.md` reviewed |
| Ledger reconciliation ready | Finance | `php artisan disputes:ledger:preview` ran clean |
| Communication plan ready | Communications | Draft email + status page copy |

## 3. Immediate actions (first 15 minutes)

1. Announce surge in `#fixit-support` and tag secondary moderators.
2. Enable dispute banner in apps: `php artisan feature:activate disputes.banner`.
3. Extend SLA timers temporarily:
   ```bash
   php artisan disputes:sla --extend=6h --incident="<INC-ID>"
   ```
4. Turn on canned responses for backlog triage (Zendesk macro `Dispute Backlog - Holding`).

## 4. Triage & routing

- **Categorise disputes** using `php artisan disputes:intake --since="-1 hour" --group-by=type`.
- **Assign owners**:
  - Payments-related → Payments SME.
  - Quality of service → Support moderators.
  - Fraudulent providers → Trust & Safety with account suspension authority.
- **Escalate high-risk** (chargebacks, legal threats) to Incident Commander.

## 5. Resolution workflow

1. **Evidence collection:** ensure service proofs, chat transcripts, and invoices attached in case file.
2. **Decision tree:** follow `docs/support/disputes-decision-tree.md` for refund vs partial payout.
3. **Ledger actions:**
   ```bash
   php artisan disputes:settlement --dispute=<ID> --decision=<approve|reject|partial> --note="<summary>"
   ```
4. **Notifications:** system triggers emails/push; verify via Notification logs `php artisan notifications:recent --channel=disputes`.
5. **Update CRM:** mark dispute state and resolution timestamp.

## 6. Monitoring & reporting

- Dashboard: Grafana `Support / Disputes` (open count, SLA at risk, decision time p95).
- Finance: confirm ledger adjustments posted correctly (`SELECT * FROM ledger_entries WHERE reference_type='dispute' ORDER BY created_at DESC LIMIT 20;`).
- Daily summary to leadership with counts, refund totals, outstanding blockers.

## 7. Wind-down & retrospective

1. Revert SLA extension: `php artisan disputes:sla --reset`.
2. Disable dispute banner once backlog < threshold.
3. Archive surge Slack channel.
4. Capture lessons in post-incident review; update training materials.

## 8. Document history

| Date | Scenario | Change summary | Reviewer |
| --- | --- | --- | --- |
| 2025-09-06 | Stage-31 | Initial disputes surge runbook documented. | @support-lead |
