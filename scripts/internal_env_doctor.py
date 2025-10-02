#!/usr/bin/env python3
"""Helper script for env_secrets_doctor.sh to validate checklist JSON."""
from __future__ import annotations

import datetime as _dt
import json
import sys
from typing import Iterable, List, Tuple

SeverityRecord = Tuple[str, str, str]


def _iso_to_datetime(value: str) -> _dt.datetime:
    try:
        if value.endswith("Z"):
            value = value[:-1] + "+00:00"
        return _dt.datetime.fromisoformat(value)
    except Exception as exc:  # noqa: BLE001
        raise ValueError(f"Invalid ISO-8601 timestamp: {value}") from exc


def _validate_structure(data: dict) -> List[SeverityRecord]:
    issues: List[SeverityRecord] = []
    namespaces = data.get("namespaces")
    secrets = data.get("secrets")

    if not isinstance(namespaces, list):
        issues.append(("ERROR", "checklist", "`namespaces` must be a list."))
        namespaces = []
    if not isinstance(secrets, list):
        issues.append(("ERROR", "checklist", "`secrets` must be a list."))
        secrets = []

    secret_map = {}
    for secret in secrets:
        if not isinstance(secret, dict):
            issues.append(("ERROR", "checklist", "Secret entry must be an object."))
            continue
        secret_id = secret.get("id")
        if not secret_id:
            issues.append(("ERROR", "checklist", "Secret entry missing `id`."))
            continue
        if secret_id in secret_map:
            issues.append(("ERROR", f"secret:{secret_id}", "Duplicate secret id."))
            continue
        secret_map[secret_id] = secret

    for namespace in namespaces:
        if not isinstance(namespace, dict):
            issues.append(("ERROR", "checklist", "Namespace entry must be an object."))
            continue
        ns_id = namespace.get("id", "unknown")
        refs = namespace.get("secret_refs", [])
        if not refs:
            issues.append(("WARNING", f"namespace:{ns_id}", "Namespace has no secret references."))
        for ref in refs:
            if ref not in secret_map:
                issues.append(("ERROR", f"namespace:{ns_id}", f"Secret reference '{ref}' not defined."))
        validations = namespace.get("validations", [])
        if not validations:
            issues.append(("WARNING", f"namespace:{ns_id}", "Namespace missing validation commands."))

    return issues


def _evaluate_secrets(
    data: dict,
    mode: str,
    targets: Iterable[str],
    now: _dt.datetime,
) -> List[SeverityRecord]:
    issues: List[SeverityRecord] = []
    secrets = data.get("secrets", [])
    secret_map = {secret.get("id"): secret for secret in secrets if isinstance(secret, dict) and secret.get("id")}

    target_set = {target for target in targets if target}
    for target in target_set:
        if target not in secret_map:
            issues.append(("ERROR", "checklist", f"Requested check target '{target}' not found."))

    for secret_id, secret in secret_map.items():
        scope = f"secret:{secret_id}"
        next_due_raw = secret.get("next_rotation_due_at")
        last_validated_raw = secret.get("last_validated_at")
        rotation_interval = secret.get("rotation_interval_days")
        criticality = (secret.get("criticality") or "tier2").lower()

        try:
            next_due = _iso_to_datetime(next_due_raw) if next_due_raw else None
        except ValueError as exc:
            issues.append(("ERROR", scope, str(exc)))
            next_due = None
        try:
            last_validated = _iso_to_datetime(last_validated_raw) if last_validated_raw else None
        except ValueError as exc:
            issues.append(("ERROR", scope, str(exc)))
            last_validated = None

        if next_due is None:
            issues.append(("ERROR", scope, "Missing `next_rotation_due_at`."))
        else:
            delta_days = (next_due - now).total_seconds() / 86400
            if delta_days < 0:
                issues.append(("ERROR", scope, f"Rotation overdue by {abs(delta_days):.0f} day(s)."))
            elif delta_days <= 7:
                severity = "ERROR" if mode == "ci" or criticality in {"tier0", "tier1"} else "WARNING"
                issues.append((severity, scope, f"Rotation due in {delta_days:.0f} day(s)."))

        if last_validated is None:
            issues.append(("ERROR", scope, "Missing `last_validated_at`."))
        elif rotation_interval:
            age_days = (now - last_validated).total_seconds() / 86400
            threshold = rotation_interval
            if age_days > threshold:
                severity = "ERROR" if criticality in {"tier0", "tier1"} else "WARNING"
                issues.append((severity, scope, f"Last validation {age_days:.0f} day(s) ago exceeds interval {threshold} day(s)."))

        validators = secret.get("validators", [])
        if not validators:
            issues.append(("WARNING", scope, "No validators defined for secret."))

    return issues


def _check_metadata(data: dict, mode: str, now: _dt.datetime) -> List[SeverityRecord]:
    issues: List[SeverityRecord] = []
    generated_at_raw = data.get("generated_at")
    if not generated_at_raw:
        issues.append(("ERROR", "checklist", "Missing `generated_at` timestamp."))
        return issues

    try:
        generated_at = _iso_to_datetime(generated_at_raw)
    except ValueError as exc:
        issues.append(("ERROR", "checklist", str(exc)))
        return issues

    age_days = (now - generated_at).total_seconds() / 86400
    if age_days > 7:
        severity = "ERROR" if mode == "ci" else "WARNING"
        issues.append((severity, "checklist", f"Checklist generated {age_days:.0f} day(s) ago; refresh required."))
    return issues


def main(argv: List[str]) -> int:
    if len(argv) < 2:
        raise SystemExit("Usage: internal_env_doctor.py <checklist.json> <mode> [checks...]")

    checklist_path = argv[0]
    mode = argv[1] if len(argv) > 1 else "local"
    targets = argv[2:]
    now = _dt.datetime.now(_dt.timezone.utc)

    with open(checklist_path, "r", encoding="utf-8") as fp:
        data = json.load(fp)

    issues: List[SeverityRecord] = []
    issues.extend(_validate_structure(data))
    issues.extend(_check_metadata(data, mode, now))
    issues.extend(_evaluate_secrets(data, mode, targets, now))

    exit_code = 0
    for severity, scope, message in issues:
        print(f"{severity}::{scope}::{message}")
        if severity.upper() == "ERROR":
            exit_code = 1

    return exit_code


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
