#!/usr/bin/env python3
"""Validate architecture governance assets and appendix structure."""
from __future__ import annotations

import argparse
import datetime as dt
import json
import re
import sys
from dataclasses import dataclass
from pathlib import Path
from typing import Dict, Iterable, List, Mapping, MutableMapping, Sequence

ROOT = Path(__file__).resolve().parent.parent
DEFAULT_ASSET = (
    ROOT
    / "apps"
    / "provider"
    / "assets"
    / "config"
    / "architecture_governance_checklist.json"
)
DEFAULT_APPENDIX = (
    ROOT / "docs" / "appendices" / "appendix-d-architecture-governance.md"
)

ISO_QUARTER_PATTERN = re.compile(r"^20[0-9]{2}-Q[1-4]$")
REQUIRED_APPENDIX_HEADERS = [
    "## 1. Charter & RACI Matrix",
    "## 2. Cadence & Intake Model",
    "## 3. Decision Records & Evidence",
    "## 4. Capability Roadmap & Dependency Operations",
    "## 5. Mobile Governance Dashboard",
    "## 6. Automation & Quality Gates",
    "## 7. Continuous Improvement",
]


@dataclass
class Issue:
    severity: str
    scope: str
    message: str

    def to_dict(self) -> Mapping[str, str]:
        return {"severity": self.severity, "scope": self.scope, "message": self.message}


class GovernanceDoctor:
    def __init__(
        self,
        asset_path: Path,
        appendix_path: Path,
        *,
        now: dt.datetime | None = None,
    ) -> None:
        self.asset_path = asset_path
        self.appendix_path = appendix_path
        self.now = now or dt.datetime.now(dt.timezone.utc)
        self.issues: List[Issue] = []

    # ------------------------------------------------------------------
    # Helpers
    def _parse_datetime(self, value: str, *, allow_naive: bool = False) -> dt.datetime:
        original = value
        if value.endswith("Z"):
            value = value[:-1] + "+00:00"
        try:
            parsed = dt.datetime.fromisoformat(value)
        except ValueError as exc:  # pragma: no cover - defensive guard
            raise ValueError(f"Invalid ISO-8601 timestamp: {original}") from exc
        if parsed.tzinfo is None:
            if allow_naive:
                parsed = parsed.replace(tzinfo=dt.timezone.utc)
            else:
                raise ValueError(f"Timestamp must include timezone offset: {original}")
        return parsed

    def _parse_date(self, value: str) -> dt.date:
        try:
            return dt.date.fromisoformat(value)
        except ValueError as exc:  # pragma: no cover - defensive
            raise ValueError(f"Invalid ISO-8601 date: {value}") from exc

    def _error(self, scope: str, message: str) -> None:
        self.issues.append(Issue("error", scope, message))

    def _warning(self, scope: str, message: str) -> None:
        self.issues.append(Issue("warning", scope, message))

    # ------------------------------------------------------------------
    def _load_json(self) -> MutableMapping[str, object]:
        if not self.asset_path.exists():
            raise FileNotFoundError(f"Checklist asset missing: {self.asset_path}")
        try:
            return json.loads(self.asset_path.read_text(encoding="utf-8"))
        except json.JSONDecodeError as exc:  # pragma: no cover - defensive
            raise ValueError(f"Invalid JSON in {self.asset_path}: {exc}") from exc

    def _ensure_appendix_headers(self) -> None:
        if not self.appendix_path.exists():
            self._error("appendix", f"Appendix file missing: {self.appendix_path}")
            return
        content = self.appendix_path.read_text(encoding="utf-8")
        for header in REQUIRED_APPENDIX_HEADERS:
            if header not in content:
                self._error("appendix", f"Missing required section header: {header}")

    # ------------------------------------------------------------------
    def validate(self) -> Mapping[str, object]:
        data = self._load_json()
        generated_at_raw = str(data.get("generated_at"))
        generated_at = self._parse_datetime(generated_at_raw)

        self._ensure_appendix_headers()

        board = data.get("board")
        members_by_id: Dict[str, Mapping[str, object]] = {}
        voting_members = 0
        if not isinstance(board, Mapping):
            self._error("board", "`board` must be an object.")
            board = {}
        else:
            quorum = board.get("quorum")
            if not isinstance(quorum, int) or quorum <= 0:
                self._error("board", "`board.quorum` must be a positive integer.")
            members = board.get("members")
            if not isinstance(members, list) or not members:
                self._error("board", "`board.members` must be a non-empty list.")
                members = []
            seen_member_ids: set[str] = set()
            for raw_member in members:
                if not isinstance(raw_member, Mapping):
                    self._error("board", "Board member entry must be an object.")
                    continue
                member_id = str(raw_member.get("id"))
                if not member_id or member_id == "None":
                    self._error("board", "Board member missing `id`.")
                    continue
                if member_id in seen_member_ids:
                    self._error("board", f"Duplicate board member id: {member_id}")
                    continue
                seen_member_ids.add(member_id)
                members_by_id[member_id] = raw_member
                is_voting = bool(raw_member.get("voting_member", False))
                if is_voting:
                    voting_members += 1
                tenure = raw_member.get("tenure_start")
                if not isinstance(tenure, str):
                    self._error("board", f"Member {member_id} missing `tenure_start`.")
                else:
                    try:
                        tenure_date = self._parse_date(tenure)
                        if tenure_date > generated_at.date():
                            self._error(
                                "board",
                                f"Member {member_id} tenure start {tenure} is in the future relative to generated_at.",
                            )
                    except ValueError as exc:
                        self._error("board", str(exc))
                responsibilities = raw_member.get("responsibilities")
                if not isinstance(responsibilities, list) or not responsibilities:
                    self._error(
                        "board",
                        f"Member {member_id} requires at least one responsibility entry.",
                    )
            if isinstance(quorum, int) and quorum > voting_members:
                self._error(
                    "board",
                    f"Quorum {quorum} exceeds voting member count {voting_members}.",
                )
            charter_review = board.get("charter_review_date")
            if isinstance(charter_review, str):
                try:
                    review_date = self._parse_date(charter_review)
                    if review_date < generated_at.date():
                        self._warning(
                            "board",
                            "Charter review date is in the past; ensure follow-up evidence is attached.",
                        )
                except ValueError as exc:
                    self._error("board", str(exc))
            else:
                self._error("board", "`charter_review_date` must be a date string.")
            next_session = board.get("next_session")
            if isinstance(next_session, str):
                try:
                    next_session_dt = self._parse_datetime(next_session)
                    if next_session_dt < generated_at:
                        self._warning(
                            "board",
                            "Next session timestamp is before generated_at; regenerate the checklist after the upcoming meeting.",
                        )
                except ValueError as exc:
                    self._error("board", str(exc))
            else:
                self._error("board", "`next_session` must be a timestamp string.")

        # Validate cadences
        cadences = data.get("cadences")
        if not isinstance(cadences, Mapping):
            self._error("cadences", "`cadences` must be an object with cadence definitions.")
            cadences = {}
        for cadence_id, cadence in cadences.items():
            scope = f"cadence:{cadence_id}"
            if not isinstance(cadence, Mapping):
                self._error(scope, "Cadence entry must be an object.")
                continue
            frequency = cadence.get("frequency_days")
            if not isinstance(frequency, int) or frequency <= 0:
                self._error(scope, "`frequency_days` must be a positive integer.")
            facilitator = cadence.get("facilitator")
            if facilitator not in members_by_id:
                self._error(scope, f"Facilitator `{facilitator}` is not a registered board member.")
            last_held = cadence.get("last_held")
            if isinstance(last_held, str):
                try:
                    held_dt = self._parse_datetime(last_held)
                    delta = (generated_at - held_dt).total_seconds() / 86400
                    if isinstance(frequency, int):
                        if delta > frequency + 1:
                            self._error(scope, "Cadence is stale relative to its frequency.")
                        elif delta > frequency:
                            self._warning(scope, "Cadence exceeded frequency by less than one day; monitor scheduling.")
                except ValueError as exc:
                    self._error(scope, str(exc))
            else:
                self._error(scope, "`last_held` must be an ISO timestamp string.")

        # Validate decisions
        decisions = data.get("decisions")
        if not isinstance(decisions, list) or not decisions:
            self._error("decisions", "`decisions` must be a non-empty list.")
            decisions = []
        linked_capability_ids: set[str] = set()
        for decision in decisions:
            if not isinstance(decision, Mapping):
                self._error("decisions", "Decision entry must be an object.")
                continue
            decision_id = str(decision.get("id"))
            owner = decision.get("owner")
            if owner not in members_by_id:
                self._error(
                    "decisions",
                    f"Decision {decision_id} owner `{owner}` is not a board member.",
                )
            status = decision.get("status")
            if status not in {"accepted", "proposed", "superseded"}:
                self._error("decisions", f"Decision {decision_id} has invalid status `{status}`.")
            renewal = decision.get("renewal_date")
            if isinstance(renewal, str):
                try:
                    renewal_date = self._parse_date(renewal)
                    days_to_renewal = (renewal_date - generated_at.date()).days
                    quality = data.get("quality_gates", {})
                    warning_window = int(quality.get("decision_renewal_warning_days", 30))
                    if days_to_renewal < 0:
                        self._error(
                            "decisions",
                            f"Decision {decision_id} renewal date {renewal} is overdue.",
                        )
                    elif days_to_renewal <= warning_window:
                        self._warning(
                            "decisions",
                            f"Decision {decision_id} renewal occurs in {days_to_renewal} days.",
                        )
                except (ValueError, TypeError) as exc:
                    self._error("decisions", f"Decision {decision_id} invalid renewal date: {exc}")
            else:
                self._error("decisions", f"Decision {decision_id} missing renewal date.")
            linked = decision.get("linked_capabilities", [])
            if not isinstance(linked, list):
                self._error("decisions", f"Decision {decision_id} linked_capabilities must be a list.")
                linked = []
            for capability_id in linked:
                linked_capability_ids.add(str(capability_id))
            evidence = decision.get("evidence")
            if not isinstance(evidence, Mapping) or not evidence.get("repository"):
                self._error("decisions", f"Decision {decision_id} missing evidence repository.")
            artifacts = evidence.get("artifacts") if isinstance(evidence, Mapping) else None
            if not isinstance(artifacts, list) or not artifacts:
                self._warning("decisions", f"Decision {decision_id} evidence artifacts list is empty.")

        # Validate capabilities
        capabilities = data.get("capabilities")
        if not isinstance(capabilities, list) or not capabilities:
            self._error("capabilities", "`capabilities` must be a non-empty list.")
            capabilities = []
        capability_ids: List[str] = []
        dependencies_covered = 0
        dependencies_total = 0
        stale_capabilities: List[str] = []
        quality_gates = data.get("quality_gates", {})
        stale_days_threshold = int(quality_gates.get("readiness_stale_days", 7))
        for capability in capabilities:
            if not isinstance(capability, Mapping):
                self._error("capabilities", "Capability entry must be an object.")
                continue
            capability_id = str(capability.get("id"))
            if not capability_id or capability_id == "None":
                self._error("capabilities", "Capability missing `id`.")
                continue
            if capability_id in capability_ids:
                self._error("capabilities", f"Duplicate capability id: {capability_id}")
                continue
            capability_ids.append(capability_id)
            owners = capability.get("owners")
            if not isinstance(owners, list) or not owners:
                self._error("capabilities", f"Capability {capability_id} missing owners list.")
            else:
                for owner_id in owners:
                    if owner_id not in members_by_id:
                        self._error(
                            "capabilities",
                            f"Capability {capability_id} owner `{owner_id}` not registered as board member.",
                        )
            quarter = capability.get("roadmap_quarter")
            if not isinstance(quarter, str) or not ISO_QUARTER_PATTERN.match(quarter):
                self._error("capabilities", f"Capability {capability_id} has invalid roadmap_quarter `{quarter}`.")
            dependencies = capability.get("dependencies", [])
            if not isinstance(dependencies, list):
                self._error(
                    "capabilities",
                    f"Capability {capability_id} dependencies must be a list.",
                )
                dependencies = []
            for dependency in dependencies:
                dependencies_total += 1
                dep_id = str(dependency)
                if dep_id == capability_id:
                    self._error(
                        "capabilities",
                        f"Capability {capability_id} cannot depend on itself.",
                    )
                if dep_id in capability_ids:
                    dependencies_covered += 1
            readiness = capability.get("readiness")
            if not isinstance(readiness, Mapping):
                self._error(
                    "capabilities",
                    f"Capability {capability_id} readiness must be an object.",
                )
            else:
                last_updated_raw = readiness.get("last_updated")
                if isinstance(last_updated_raw, str):
                    try:
                        last_updated = self._parse_datetime(last_updated_raw)
                        delta_days = (generated_at - last_updated).total_seconds() / 86400
                        if delta_days > stale_days_threshold:
                            self._error(
                                "capabilities",
                                f"Capability {capability_id} readiness is stale by {delta_days:.1f} days.",
                            )
                            stale_capabilities.append(capability_id)
                        elif delta_days > stale_days_threshold - 1:
                            self._warning(
                                "capabilities",
                                f"Capability {capability_id} readiness approaching staleness threshold.",
                            )
                    except ValueError as exc:
                        self._error("capabilities", f"Capability {capability_id} invalid last_updated: {exc}")
                else:
                    self._error(
                        "capabilities",
                        f"Capability {capability_id} readiness missing last_updated timestamp.",
                    )
            metrics = capability.get("metrics")
            if not isinstance(metrics, list) or not metrics:
                self._error(
                    "capabilities",
                    f"Capability {capability_id} requires at least one metric.",
                )
            else:
                for metric in metrics:
                    if not isinstance(metric, Mapping):
                        self._error(
                            "capabilities",
                            f"Capability {capability_id} metric entry must be an object.",
                        )
                        continue
                    if not metric.get("id"):
                        self._error(
                            "capabilities",
                            f"Capability {capability_id} metric missing id.",
                        )
                    baseline = metric.get("baseline")
                    target = metric.get("target")
                    if not isinstance(baseline, (int, float)) or not isinstance(
                        target, (int, float)
                    ):
                        self._error(
                            "capabilities",
                            f"Capability {capability_id} metric must define numeric baseline/target.",
                        )
                    elif baseline == target:
                        self._warning(
                            "capabilities",
                            f"Capability {capability_id} metric {metric.get('id')} baseline equals target; validate ambition.",
                        )

        # Cross-reference decisions to capabilities
        for linked_id in linked_capability_ids:
            if linked_id not in capability_ids:
                self._error(
                    "decisions",
                    f"Decision references unknown capability `{linked_id}`.",
                )

        # Validate risk register
        risk_register = data.get("risk_register", [])
        if not isinstance(risk_register, list):
            self._error("risk_register", "`risk_register` must be a list.")
            risk_register = []
        for risk in risk_register:
            if not isinstance(risk, Mapping):
                self._error("risk_register", "Risk entry must be an object.")
                continue
            owner = risk.get("owner")
            if owner not in members_by_id:
                self._warning(
                    "risk_register",
                    f"Risk `{risk.get('id')}` owner `{owner}` not listed as board member.",
                )
            due_date = risk.get("due_date")
            if isinstance(due_date, str):
                try:
                    due = self._parse_date(due_date)
                    if due < generated_at.date():
                        self._warning(
                            "risk_register",
                            f"Risk `{risk.get('id')}` due date {due_date} is past due.",
                        )
                except ValueError as exc:
                    self._error("risk_register", str(exc))
            else:
                self._error("risk_register", "Risk entries must include `due_date`.")

        # Compute dependency coverage metric
        dependency_coverage = 1.0
        if dependencies_total > 0:
            dependency_coverage = dependencies_covered / dependencies_total

        report = {
            "generated_at": generated_at.isoformat(),
            "board_member_count": len(members_by_id),
            "voting_member_count": voting_members,
            "capability_count": len(capability_ids),
            "dependency_coverage": dependency_coverage,
            "issues": [issue.to_dict() for issue in self.issues],
        }
        return report


def parse_args(argv: Sequence[str] | None = None) -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Validate governance assets.")
    parser.add_argument("--asset", type=Path, default=DEFAULT_ASSET)
    parser.add_argument("--appendix", type=Path, default=DEFAULT_APPENDIX)
    return parser.parse_args(argv)


def main(argv: Sequence[str] | None = None) -> int:
    args = parse_args(argv)
    doctor = GovernanceDoctor(args.asset, args.appendix)
    try:
        report = doctor.validate()
    except Exception as exc:  # noqa: BLE001
        print(json.dumps({"status": "error", "error": str(exc)}))
        return 1
    severity = 0
    for issue in report["issues"]:
        if issue["severity"].lower() == "error":
            severity = 1
            break
    status = "pass" if severity == 0 else "fail"
    payload = {
        "status": status,
        **report,
    }
    print(json.dumps(payload, indent=2, sort_keys=True))
    return severity


if __name__ == "__main__":
    sys.exit(main())
