# Payments & Payouts Recovery Runbook

**Audience:** Payments engineering, Finance operations, Trust & Safety  
**Systems:** Stripe Connect, internal ledgers (`ledger_entries`, `provider_payouts`), webhooks, Horizon workers.

## 1. Activation scenarios

Run this playbook when:

- Stripe webhook backlog > 5 minutes or retries > 3 (Stripe dashboard alert `webhook_delivery_latency`).
- Ledger mismatch > $100 or > 0.5% of daily GMV (`php artisan payments:reconcile` report).
- Provider payouts stuck in `pending` for > 24h.
- Fraud investigation requiring payout freeze (Trust & Safety directive).

## 2. Readiness checklist

| Check | Command | Status |
| --- | --- | --- |
| Access to Stripe dashboard (production) confirmed | https://dashboard.stripe.com/ |  |
| Finance Ops on bridge | Slack ping |  |
| Latest database backup timestamp | `aws rds describe-db-instances --db-instance-identifier fixit-prod` |  |
| Outbox queue depth | `php artisan outbox:metrics` |  |

## 3. Immediate containment

1. **Freeze outbound payouts (if fraud suspected):**
   ```bash
   php artisan payouts:freeze --reason="incident <INC-ID>"
   ```
2. **Pause webhook processing (only if duplicate processing risk):**
   ```bash
   php artisan horizon:pause payments
   ```
3. **Notify stakeholders:** Finance ops, Trust & Safety, and Communications lead.

## 4. Diagnosis workflow

1. Run reconciliation for the affected window:
   ```bash
   php artisan payments:reconcile --since="-24 hours" --format=json > storage/logs/reconcile.json
   ```
2. Diff ledger vs Stripe:
   ```bash
   jq '.mismatches[] | {type, stripe_id, internal_id, delta}' storage/logs/reconcile.json
   ```
3. Inspect webhook DLQ (SQS):
   ```bash
   aws sqs get-queue-attributes --queue-url $WEBHOOK_DLQ --attribute-names ApproximateNumberOfMessages
   ```
4. For payouts stuck in `pending`, query database:
   ```sql
   SELECT id, provider_id, status, scheduled_for, failure_reason
   FROM provider_payouts
   WHERE status = 'pending' AND scheduled_for < NOW() - INTERVAL 12 HOUR;
   ```

## 5. Remediation paths

### 5.1 Webhook backlog

- Resume Horizon queue after verifying Stripe delivery health:
  ```bash
  php artisan horizon:continue payments
  ```
- Replay failed events:
  ```bash
  php artisan payments:webhook:replay --since="-6 hours"
  ```
- Monitor: Stripe dashboard `Developers > Webhooks`, expect delivery latency < 60s.

### 5.2 Ledger mismatch

- Generate adjustment entries with runbook-approved script:
  ```bash
  php artisan payments:adjust-ledger --input=storage/logs/reconcile.json
  ```
- Peer review adjustment diff, attach to incident ticket.
- Post-adjustment reconciliation must return zero mismatches.

### 5.3 Payout failures

- Investigate failure codes via Stripe API:
  ```bash
  stripe transfers retrieve tr_xxx
  ```
- For account issues, notify provider via templated email `emails/payments/payout_failed.blade.php`.
- Resume payouts after resolution:
  ```bash
  php artisan payouts:resume --segment=providers --limit=100
  ```

### 5.4 Fraud or regulatory hold

- Tag affected providers: `php artisan providers:tag --ids=<ids> --tag=frozen_due_incident`.
- Coordinate with Trust & Safety for SAR filings if needed.
- Document evidence trail in secure drive (SOC2 requirement).

## 6. Verification

- `php artisan payments:reconcile --since="-2 hours"` returns `status: clean`.
- Stripe dashboard shows zero pending payouts and webhook delivery success >= 99%.
- Finance signs off on updated ledger snapshot.
- Incident ticket updated with resolution summary and adjustments applied.

## 7. Rollback / fallback

- If adjustments introduce regression, run `php artisan payments:adjust-ledger --input=<backup>` with inverse values.
- Restore database snapshot (RDS) only if instructed by SRE leadership.
- Keep payouts frozen until compliance gives written approval.

## 8. Document history

| Date | Trigger | Change summary | Reviewer |
| --- | --- | --- | --- |
| 2025-09-06 | Stage-31 | Initial payments recovery runbook. | @payments-lead |
