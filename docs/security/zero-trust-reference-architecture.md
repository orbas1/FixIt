# Zero-Trust Reference Architecture & Control Mapping

## Purpose

This document codifies the zero-trust architecture for FixIt, aligning identity, network, device, and data controls with NIST SP 800-207, CIS v8, and ISO 27001 Annex A expectations. It defines enforcement guardrails, assurance evidence, and remediation workflows for security operations, engineering, and compliance teams.

## Pillars & Control Families

| Pillar | Objectives | Primary Controls | Standards Mapping |
| --- | --- | --- | --- |
| **Identity** | Authenticate every identity, enforce contextual MFA, and continuously authorize sessions. | Laravel Sanctum tokens, adaptive MFA (TOTP, recovery codes), zero-trust evaluator for conditional access. | NIST IA-2, IA-11, CIS 6.3, ISO 27001 A.5.17 |
| **Device** | Verify device hygiene, restrict untrusted hardware, and quarantine compromised endpoints. | Trusted device ledger, device trust levels, session telemetry enrichment (platform, last seen, risk score). | NIST CM-8, CIS 1.1, ISO 27001 A.8.1 |
| **Network** | Segment access based on network zones, enforce least privilege, and detect anomalous travel. | `network_zones` registry, CIDR enforcement, impossible travel detection, automated denies. | NIST AC-17, SC-7, CIS 4.4, ISO 27001 A.8.20 |
| **Application & Data** | Gate sensitive APIs, require proven identity for privileged actions, and emit evidence. | Zero-trust middleware, evidence snapshot command, RFC7807 error contracts, privileged role MFA enforcement. | NIST AC-3, AU-6, CIS 6.7, ISO 27001 A.9.4 |

## Architecture Overview

1. **Context Collection** – API gateways forward device identifiers, IP metadata, and user agents. Middleware normalizes fingerprints and associates requests with trusted devices and network zones.
2. **Risk Engine** – `ZeroTrustEvaluator` scores sessions using configurable thresholds, stale MFA detection, network segmentation, and historical access telemetry. Decisions (`allow`, `challenge`, `deny`) are persisted in the `zero_trust_access_events` ledger.
3. **Policy Enforcement** – `EnforceZeroTrust` middleware intercepts authenticated traffic, short-circuits on `challenge`/`deny`, and emits RFC7807 responses with detailed signals. Auth flows perform pre-login evaluation to block high-risk access and enforce MFA enrollment for privileged roles.
4. **Micro-Segmentation** – Admin APIs manage `network_zones` with CIDR coverage, enforced controls, and risk ratings. Middleware maps inbound IPs to zones and augments risk scoring.
5. **Assurance & Evidence** – `security:zero-trust:evidence` aggregates daily metrics, top signals, and enforced controls, exporting JSON artefacts to `storage/app/security/zero-trust/YYYY/MM/DD/`. Evidence feeds executive scorecards and audit readiness packages.

## Gap Remediation & Hardening Backlog

| Gap | Remediation | Owner | Status |
| --- | --- | --- | --- |
| Privileged roles without MFA | Block login with RFC7807 `mfa-required` response, enroll via security runbook. | Identity Ops | ✅ Implemented |
| Untrusted network ingress | Deny traffic from `blocked` zones, challenge `restricted` zones, notify SOC. | Security Ops | ✅ Implemented |
| New/expired devices bypassing controls | Trusted device TTL + forced re-approval on stale devices. | Endpoint Sec | ✅ Implemented |
| Evidence automation | Scheduled zero-trust evidence snapshots, retention 365 days. | GRC | ✅ Implemented |
| Real-time monitoring | Stream access events to SIEM via future queue integration. | SecEng | 🔄 Backlog |

## Operational Guidance

1. **Enrollment** – Identity operations configure MFA enrollment campaigns for all roles listed in `zero_trust.enforcement.require_mfa_roles`.
2. **Incident Response** – SOC analysts review `zero_trust_events` API when responding to identity alerts. Denied events contain full signal payloads for triage.
3. **Change Management** – Security architecture board reviews policy changes (risk thresholds, network zones) and records decision logs in governance tooling.
4. **Audit Readiness** – Evidence snapshots and network zone definitions are included in SOC2/ISO documentation, enabling cross-functional attestation.
5. **Mobile Alignment** – Flutter login flows honor zero-trust challenge responses, ensuring parity across web and mobile surfaces.

## Key Metrics

* Challenge Rate (rolling 7-day): target < 4% of successful logins.
* Deny Rate: target < 0.25%, with root-cause analysis for each incident.
* MFA Enrollment Coverage: 100% for admin, finance, dispute, and operations roles.
* Device Freshness: ≥ 95% of trusted devices seen within 30 days.
* Evidence Freshness: zero-trust snapshot produced daily before 03:00 UTC.

## References

* [NIST SP 800-207 Zero Trust Architecture](https://csrc.nist.gov/publications/detail/sp/800-207/final)
* [CIS Controls v8 Implementation Group 2](https://www.cisecurity.org/controls/cis-controls-list)
* [ISO/IEC 27001:2022 Annex A – Controls](https://www.iso.org/isoiec-27001-information-security.html)
* FixIt Incident Response Playbooks (`docs/runbooks/`)
* FixIt Architecture Governance Charter (`docs/appendices/appendix-d-architecture-governance.md`)
