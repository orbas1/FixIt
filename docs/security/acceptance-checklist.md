# Security Incident Response Acceptance Checklist

This checklist validates feature-complete delivery of FixIt security incident response (tasks 7.9–7.10).

## Automated tests

- `php artisan test --filter SecurityIncidentControllerTest`
  - Confirms incident reporting, acknowledgement, resolution, and closure flows.
  - Verifies notifications are dispatched to administrative watchers.
- API contract validation via Postman collection `security-incidents.postman_collection.json` (stored in the QA repository).
- Load tests: `k6 run scripts/security-incidents.js` maintains <200ms p95 latency for incident listing under 100 concurrent responders.

## Manual verification

1. **Role-based access control**
   - Admin user can view/create/update incidents.
   - Provider/consumer roles receive HTTP 403 responses when attempting to access `/api/v1/security/incidents`.
2. **Timeline integrity**
   - Each state transition appends an event with timestamp and actor metadata.
   - No duplicate entries when actions are retried.
3. **Notification delivery**
   - Ensure emails render subject/body as expected for `reported`, `acknowledged`, `resolved`, and `closed` events.
   - Confirm in-app notification list surfaces the incident payload.
4. **Runbook linkage**
   - `runbook_updates` array renders in admin UI with section + change log.
   - Linked runbook pages load without authentication errors.
5. **Metrics integration**
   - Data warehouse job ingests `/api/v1/security/incidents` feed nightly (check Airflow `security_incident_etl` DAG).
   - MTTA/MTTR dashboards update with latest incident data within 30 minutes.

## Acceptance sign-off

- [ ] Security Engineering Lead
- [ ] Site Reliability Engineering Lead
- [ ] Compliance Officer
- [ ] Product Operations Manager

Attach the completed checklist to the incident closure note and store in the compliance document repository.
