#!/usr/bin/env python3
import json
import sys
from pathlib import Path

payload = json.load(sys.stdin)

asset_count = payload.get("asset_count", 0)
non_compliant = payload.get("non_compliant", 0)
attention_required = payload.get("attention_required", 0)
items = payload.get("items", [])

issues = []

if asset_count != len(items):
    issues.append(
        f"Asset count mismatch: header reports {asset_count} but payload contains {len(items)} entries."
    )

for item in items:
    if item.get("status") not in {"compliant", "attention_required", "non_compliant"}:
        issues.append(f"Unexpected status for {item.get('asset')}: {item.get('status')}")
    if not isinstance(item.get("issues"), list):
        issues.append(f"Issues payload for {item.get('asset')} must be a list.")
    if item.get("requires_dpia") and item.get("dpia_records", 0) == 0:
        issues.append(f"Asset {item.get('asset')} requires a DPIA but has no records registered.")

if non_compliant > 0:
    assets = [i.get("asset") for i in items if i.get("status") == "non_compliant"]
    issues.append(
        "Non-compliant assets detected: " + ", ".join(assets)
    )

if issues:
    sys.stderr.write("Data governance doctor detected issues:\n")
    for msg in issues:
        sys.stderr.write(f" - {msg}\n")
    sys.exit(1)

summary = {
    "asset_count": asset_count,
    "attention_required": attention_required,
    "non_compliant": non_compliant,
}

Path("storage/app/data_governance_doctor_report.json").write_text(json.dumps(payload, indent=2))

sys.stdout.write(json.dumps(summary) + "\n")
