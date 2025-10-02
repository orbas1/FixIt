#!/usr/bin/env python3
"""Localization and currency validation tooling."""

from __future__ import annotations

import argparse
import json
import re
import subprocess
import sys
from dataclasses import dataclass
from pathlib import Path
from typing import Dict, Iterable, List, Mapping, MutableMapping, Sequence, Set, Tuple

ROOT = Path(__file__).resolve().parent.parent
LARAVEL_LANG_DIR = ROOT / "resources" / "lang"
FLUTTER_LANG_DIR = (
    ROOT / "apps" / "provider" / "lib" / "common" / "languages"
)
CHECKLIST_DEFAULT = (
    ROOT / "apps" / "provider" / "assets" / "config" / "i18n_currency_checklist.json"
)


@dataclass(frozen=True)
class LocaleMetrics:
    code: str
    flutter_missing: Sequence[str]
    flutter_extra: Sequence[str]
    laravel_missing: Sequence[str]
    laravel_extra: Sequence[str]
    flutter_coverage: float
    laravel_coverage: float
    rtl: bool


@dataclass(frozen=True)
class CurrencySampleResult:
    amount: float
    expected: str
    actual: str
    locale: str | None
    matches: bool


@dataclass(frozen=True)
class CurrencyProfileMetrics:
    code: str
    symbol: str
    symbol_position: str
    decimal_digits: int
    decimal_separator: str
    thousands_separator: str
    samples: Sequence[CurrencySampleResult]


class DoctorError(RuntimeError):
    """Raised when validation fails."""


def _flatten_php_array(path: Path) -> Mapping[str, str]:
    path_literal = json.dumps(str(path))
    script = f"""
if (!function_exists('config')) {{
    function config($key) {{
        return $key;
    }}
}}
if (!function_exists('__')) {{
    function __($key) {{
        return $key;
    }}
}}
$data = include {path_literal};
if (!is_array($data)) {{
    $data = [];
}}
function flatten($input, $prefix = '') {{
    $result = [];
    foreach ($input as $key => $value) {{
        $composed = $prefix === '' ? $key : $prefix . '.' . $key;
        if (is_array($value)) {{
            $result = array_merge($result, flatten($value, $composed));
        }} else {{
            $result[$composed] = $value;
        }}
    }}
    return $result;
}}
echo json_encode(flatten($data), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
"""
    result = subprocess.run(
        ["php", "-r", script],
        cwd=ROOT,
        capture_output=True,
        text=True,
        check=False,
    )
    if result.returncode != 0:
        raise DoctorError(
            f"Failed to parse PHP translations at {path}: {result.stderr.strip()}"
        )
    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError as exc:  # pragma: no cover - defensive
        raise DoctorError(
            f"Invalid JSON while parsing {path}: {exc}"
        ) from exc


def _load_php_locale(locale: str) -> Mapping[str, str]:
    base_dir = LARAVEL_LANG_DIR / locale
    if not base_dir.exists():
        return {}
    merged: MutableMapping[str, str] = {}
    for file in sorted(base_dir.rglob("*.php")):
        merged.update(_flatten_php_array(file))
    return merged


_KEY_PATTERN = re.compile(r"['\"]([^'\"]+)['\"]\s*:")
_COMMENT_PATTERN = re.compile(r"//.*?$", re.MULTILINE)
_BLOCK_COMMENT_PATTERN = re.compile(r"/\*.*?\*/", re.DOTALL)


def _load_flutter_locale(locale: str) -> Set[str]:
    path = FLUTTER_LANG_DIR / f"{locale}.dart"
    if not path.exists():
        raise DoctorError(f"Missing Flutter locale file: {path}")
    content = path.read_text(encoding="utf-8")
    content = _BLOCK_COMMENT_PATTERN.sub("", content)
    content = _COMMENT_PATTERN.sub("", content)
    return set(match.group(1) for match in _KEY_PATTERN.finditer(content))


_RTL_LOCALES = {"ar", "he", "fa", "ur"}


def _compute_locale_metrics(locale: Mapping[str, object], base_flutter: Set[str], base_php: Set[str]) -> LocaleMetrics:
    code = str(locale.get("code"))
    rtl = bool(locale.get("rtl", False))
    flutter_keys = _load_flutter_locale(code)
    laravel_keys = set(_load_php_locale(code).keys())

    flutter_missing = sorted(base_flutter - flutter_keys)
    flutter_extra = sorted(flutter_keys - base_flutter)
    laravel_missing = sorted(base_php - laravel_keys)
    laravel_extra = sorted(laravel_keys - base_php)

    flutter_coverage = 1.0 if not base_flutter else 1.0 - (len(flutter_missing) / len(base_flutter))
    laravel_coverage = 1.0 if not base_php else 1.0 - (len(laravel_missing) / len(base_php))

    should_be_rtl = code.split("-")[0] in _RTL_LOCALES
    if should_be_rtl != rtl:
        raise DoctorError(
            f"Locale {code} RTL expectation mismatch: checklist={rtl} expected={should_be_rtl}"
        )

    expected = locale.get("expected", {})
    expected_flutter_raw = expected.get("flutter_coverage", 0.0)
    expected_laravel_raw = expected.get("laravel_coverage")
    expected_flutter_coverage = float(expected_flutter_raw)
    if flutter_coverage + 1e-9 < expected_flutter_coverage:
        raise DoctorError(
            f"Locale {code} Flutter coverage {flutter_coverage:.3f} below expected {expected_flutter_coverage:.3f}"
        )
    if expected_laravel_raw is not None:
        expected_laravel_coverage = float(expected_laravel_raw)
        if laravel_coverage + 1e-9 < expected_laravel_coverage:
            raise DoctorError(
                f"Locale {code} Laravel coverage {laravel_coverage:.3f} below expected {expected_laravel_coverage:.3f}"
            )
    expected_flutter_missing = sorted(expected.get("missing_flutter_keys", []))
    expected_laravel_missing = sorted(expected.get("missing_laravel_keys", []))
    if flutter_missing != expected_flutter_missing:
        raise DoctorError(
            f"Locale {code} Flutter missing keys mismatch: expected {expected_flutter_missing}, got {flutter_missing}"
        )
    if expected_laravel_raw is not None and laravel_missing != expected_laravel_missing:
        raise DoctorError(
            f"Locale {code} Laravel missing keys mismatch: expected {expected_laravel_missing}, got {laravel_missing}"
        )

    return LocaleMetrics(
        code=code,
        flutter_missing=flutter_missing,
        flutter_extra=flutter_extra,
        laravel_missing=laravel_missing,
        laravel_extra=laravel_extra,
        flutter_coverage=flutter_coverage,
        laravel_coverage=laravel_coverage,
        rtl=rtl,
    )


def _format_amount(
    amount: float,
    *,
    decimals: int,
    thousands: str,
    decimal: str,
    symbol: str,
    position: str,
) -> str:
    magnitude = abs(amount)
    digits = f"{magnitude:.{decimals}f}"
    integer_part, _, fraction_part = digits.partition(".")
    groups: List[str] = []
    for index, char in enumerate(reversed(integer_part)):
        if index and index % 3 == 0 and thousands:
            groups.append(thousands)
        groups.append(char)
    formatted_integer = "".join(reversed(groups))
    formatted = formatted_integer
    if decimals > 0:
        formatted = f"{formatted}{decimal}{fraction_part}" if fraction_part else formatted
    if position == "right":
        combined = f"{formatted}{symbol}"
    else:
        combined = f"{symbol}{formatted}"
    if amount < 0:
        return f"-{combined}"
    return combined


def _compute_currency_metrics(profile: Mapping[str, object]) -> CurrencyProfileMetrics:
    code = str(profile.get("code"))
    symbol = str(profile.get("symbol", ""))
    position = str(profile.get("symbol_position", "left")).lower()
    if position not in {"left", "right"}:
        raise DoctorError(
            f"Currency profile {code} has invalid symbol_position '{position}'"
        )
    decimal_digits = int(profile.get("decimal_digits", 2))
    if decimal_digits < 0 or decimal_digits > 6:
        raise DoctorError(
            f"Currency profile {code} decimal_digits {decimal_digits} out of range"
        )
    decimal_separator = str(profile.get("decimal_separator", "."))
    thousands_separator = str(profile.get("thousands_separator", ","))
    if not decimal_separator:
        raise DoctorError(f"Currency profile {code} missing decimal_separator")
    if not thousands_separator:
        raise DoctorError(f"Currency profile {code} missing thousands_separator")

    samples_data = profile.get("samples", [])
    if not isinstance(samples_data, list) or not samples_data:
        raise DoctorError(f"Currency profile {code} must define non-empty samples")

    samples: List[CurrencySampleResult] = []
    for sample in samples_data:
        amount = float(sample.get("amount", 0.0))
        expected = str(sample.get("expected", ""))
        locale = sample.get("locale")
        actual = _format_amount(
            amount,
            decimals=decimal_digits,
            thousands=thousands_separator,
            decimal=decimal_separator,
            symbol=symbol,
            position=position,
        )
        matches = actual == expected
        if not matches:
            raise DoctorError(
                f"Currency profile {code} sample {amount} mismatch: expected '{expected}' got '{actual}'"
            )
        samples.append(
            CurrencySampleResult(
                amount=amount,
                expected=expected,
                actual=actual,
                locale=str(locale) if locale else None,
                matches=matches,
            )
        )

    return CurrencyProfileMetrics(
        code=code,
        symbol=symbol,
        symbol_position=position,
        decimal_digits=decimal_digits,
        decimal_separator=decimal_separator,
        thousands_separator=thousands_separator,
        samples=samples,
    )


def _load_checklist(path: Path) -> Mapping[str, object]:
    try:
        with path.open(encoding="utf-8") as handle:
            return json.load(handle)
    except FileNotFoundError as exc:
        raise DoctorError(f"Checklist file not found: {path}") from exc
    except json.JSONDecodeError as exc:  # pragma: no cover - defensive
        raise DoctorError(f"Checklist JSON invalid: {path}: {exc}") from exc


@dataclass
class DoctorReport:
    locales: Mapping[str, LocaleMetrics]
    currency_profiles: Mapping[str, CurrencyProfileMetrics]
    checklist_path: Path

    def to_dict(self) -> Mapping[str, object]:
        return {
            "checklist": str(self.checklist_path.relative_to(ROOT)),
            "locales": {
                code: {
                    "flutter_missing": metrics.flutter_missing,
                    "flutter_extra": metrics.flutter_extra,
                    "laravel_missing": metrics.laravel_missing,
                    "laravel_extra": metrics.laravel_extra,
                    "flutter_coverage": round(metrics.flutter_coverage, 6),
                    "laravel_coverage": round(metrics.laravel_coverage, 6),
                    "rtl": metrics.rtl,
                }
                for code, metrics in self.locales.items()
            },
            "currency_profiles": {
                code: {
                    "symbol": metrics.symbol,
                    "symbol_position": metrics.symbol_position,
                    "decimal_digits": metrics.decimal_digits,
                    "decimal_separator": metrics.decimal_separator,
                    "thousands_separator": metrics.thousands_separator,
                    "samples": [
                        {
                            "amount": sample.amount,
                            "expected": sample.expected,
                            "actual": sample.actual,
                            "locale": sample.locale,
                            "matches": sample.matches,
                        }
                        for sample in metrics.samples
                    ],
                }
                for code, metrics in self.currency_profiles.items()
            },
        }


def run(checklist_path: Path) -> DoctorReport:
    checklist = _load_checklist(checklist_path)
    locales_data = checklist.get("locales", [])
    if not isinstance(locales_data, list) or not locales_data:
        raise DoctorError("Checklist must define locales array")

    currency_profiles_data = checklist.get("currency_profiles", [])
    if not isinstance(currency_profiles_data, list) or not currency_profiles_data:
        raise DoctorError("Checklist must define currency_profiles array")

    profile_map: Dict[str, Mapping[str, object]] = {}
    for profile in currency_profiles_data:
        code = str(profile.get("code"))
        if not code:
            raise DoctorError("Currency profile missing code")
        if code in profile_map:
            raise DoctorError(f"Duplicate currency profile code: {code}")
        profile_map[code] = profile

    base_flutter = _load_flutter_locale("en")
    base_php = set(_load_php_locale("en").keys())

    locale_metrics: Dict[str, LocaleMetrics] = {}
    for locale in locales_data:
        code = str(locale.get("code"))
        if not code:
            raise DoctorError("Locale entry missing code")
        metrics = _compute_locale_metrics(locale, base_flutter, base_php)
        locale_metrics[code] = metrics
        profile_code = str(locale.get("currency_profile"))
        if profile_code not in profile_map:
            raise DoctorError(
                f"Locale {code} references unknown currency profile '{profile_code}'"
            )

    currency_metrics: Dict[str, CurrencyProfileMetrics] = {}
    for code, profile in profile_map.items():
        currency_metrics[code] = _compute_currency_metrics(profile)

    return DoctorReport(
        locales=locale_metrics,
        currency_profiles=currency_metrics,
        checklist_path=checklist_path,
    )


def main(argv: Sequence[str]) -> int:
    parser = argparse.ArgumentParser(description="Validate i18n and currency readiness")
    parser.add_argument(
        "--checklist",
        type=Path,
        default=CHECKLIST_DEFAULT,
        help="Path to the checklist JSON asset",
    )
    parser.add_argument(
        "--json",
        action="store_true",
        help="Emit machine-readable JSON report",
    )
    args = parser.parse_args(argv)

    try:
        report = run(args.checklist)
    except DoctorError as exc:
        print(json.dumps({"status": "error", "error": str(exc)}), file=sys.stdout)
        return 1

    payload = {"status": "ok", "report": report.to_dict()}
    if args.json:
        print(json.dumps(payload, ensure_ascii=False, indent=2))
    else:
        print(json.dumps(payload, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
