# Blue/Green Deployment Runbook

**Audience:** Release managers, SRE, senior backend engineers  
**Systems covered:** Laravel API, Horizon workers, Flutter web assets, Redis, MySQL, S3, CloudFront  
**Environments:** Staging, Production

## 1. Purpose & success criteria

Safely deploy a new FixIt release with near-zero downtime using blue/green infrastructure (two auto-scaling groups per environment with shared database). Success is defined as:

- Database migrations completed without blocking active traffic.
- Horizon workers drained, restarted, and processing new jobs within 2 minutes.
- Canary and full-production smoke tests passing (HTTP 2xx, latency within baseline).
- No customer-facing errors above baseline 15 minutes after cutover.

## 2. Pre-deployment readiness checklist

| Check | Command / Evidence | Status |
| --- | --- | --- |
| Release branch merged & tagged | `git describe --tags` |  |
| Composer & npm packages built | `composer install --no-dev`, `npm ci && npm run build` |  |
| Docker images pushed | `aws ecr describe-images --repository-name fixit/api --image-ids imageTag=<tag>` |  |
| Database migrations reviewed | PR link in Notion / GitHub |  |
| Feature flags prepared | LaunchDarkly toggles reviewed |  |
| Incident channel pre-created | `#inc-fixit-<date>` in Slack |  |

> **If any check fails, stop and resolve before proceeding.**

## 3. Execution timeline

### 3.1 Initiate maintenance window (optional)

1. Post in `#fixit-status` with deployment window, expected impact, rollback owner.
2. Ensure on-call engineer and product owner are in the bridge call.

### 3.2 Prepare target (green) stack

```bash
aws deploy create-deployment \
  --application-name fixit-api \
  --deployment-group-name green \
  --s3-location bucket=fixit-artifacts,key=api/<tag>.zip,bundleType=zip
```

- Wait for CodeDeploy health check (ALB target group `fixit-green`) to pass.
- Tail logs: `aws logs tail /aws/elasticbeanstalk/fixit-green/var/log/eb-activity.log --follow`.

### 3.3 Database migration & cache warmup

```bash
# SSH or SSM into green leader instance
php artisan down --render=maintenance || true
php artisan migrate --force
php artisan search:reindex
php artisan geo:backfill --missing
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
php artisan up
```

Warm caches using the synthetic smoke user:

```bash
php artisan smoke:touch --scenario=provider-dashboard
php artisan smoke:touch --scenario=consumer-checkout
```

### 3.4 Canary validation

1. Shift 5% of traffic to green ALB target using weighted DNS or ALB rules.
2. Run smoke tests:
   - `k6 run k6/smoke.js --env BASE_URL=https://api-green.fixit.com`
   - `flutter drive --target=test_driver/smoke.dart --dart-define BASE_URL=https://api-green.fixit.com`
3. Monitor dashboards (New Relic APM, CloudWatch metrics `HTTP 5xx`, `Latency P95`).
4. If failures occur, rollback traffic to blue and investigate (see §6).

### 3.5 Full cutover

1. Shift 100% traffic to green.
2. Verify Horizon queues:
   ```bash
   php artisan horizon:status
   php artisan horizon:supervisors
   ```
3. Decommission blue nodes once metrics stabilise (15 minutes steady-state). Keep at least one node in standby for one hour.

## 4. Observability & validation

- **APM:** New Relic dashboard `FixIt / Prod` – latency, throughput, error rate.
- **Logs:** CloudWatch Insights query `fields @timestamp, @message | filter @message like /ERROR|CRITICAL/`.
- **Queue depth:** `php artisan horizon:stats` should show `pending_jobs == 0` after restart.
- **Business KPIs:** Mixpanel monitor `orders_placed`, Stripe dashboard `Payments > Activity`.

## 5. Post-deployment tasks

1. Flip feature flags ON gradually according to launch plan.
2. Update release notes in Confluence, tag stakeholders.
3. Close the deployment status message with ✅ once validation passes.
4. File follow-up tickets for any manual steps that could be automated.

## 6. Rollback / contingency

- **Immediate rollback:** re-route traffic to blue stack via DNS/ALB weight reversal.
- **Database rollback:** only execute if backward-compatible migrations failed. Use `php artisan migrate:rollback --step=<n>` if safe; otherwise restore latest snapshot (`aws rds restore-db-instance-to-point-in-time`).
- **Cache purge:** `php artisan cache:clear` on both stacks to avoid stale configuration.
- **Communication:** announce rollback in `#fixit-status`, include incident ID and expected recovery timeline.

## 7. Document history

| Date | Environment | Change summary | Incident / Ticket | Reviewer |
| --- | --- | --- | --- | --- |
| 2025-09-06 | Production | Initial runbook committed with Stage 31 deliverable. | Stage-31 | @oncall |
