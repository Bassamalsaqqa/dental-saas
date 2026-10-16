#!/usr/bin/env python3
"""
scripts/capture_pr3_staging_evidence.py

Maintainable, pure stdlib tool to validate, sanitize, project, bind, and verify
local staging execution evidence for Denta+ PR #3 / PR #4 scheduling workflows.

Design & Constraints (P2 Correction):
- Python stdlib only (zero external dependencies).
- Refuses non-empty output directories; rejects overlapping or symlinked paths.
- Execution provenance: requires root-produced execution-record.json binding
  all commands, exit codes, output logs, and artifact digests.
- Field projection: explicitly projects safe metrics and EXPLAIN plans; excludes
  SQL queries, query prefixes, worker logs, actor/patient/clinic IDs, and canaries.
- Source integrity: hashes canonical LF-normalized UTF-8 source across all test,
  harness, runner, and runtime dependencies so git newline differences do not drift.
- Fail-closed verification: validates 6 core table counts (including clinical_procedures),
  exact volume expectations (55k/50k/2k), byte preservation, and plan provenance.
- Binds every artifact to identical sets in manifest.sha256 and manifest.json.
"""

from __future__ import annotations

import argparse
from datetime import datetime, timezone
import hashlib
import math
import stat
import json
import os
from pathlib import Path
import platform
import re
import sys
from typing import Any, Dict, List, Optional, Set
import unittest


# Fixed narrow allowlist of generated artifacts in committed evidence
FIXED_ARTIFACT_NAMES = [
    "staging-final-php.log",
    "staging-final-mobile.log",
    "pr3-staging-scale.json",
    "pr3-charge-index-assessment.json",
    "environment.json",
]

OPTIONAL_ARTIFACT_NAMES = [
    "pr3-staging-concurrency.json",
    "pr3-staging-rollback.json",
]

# Required 6 tables in scale preservation verification
CORE_TABLES = [
    "appointments",
    "clinical_charge_lines",
    "patients",
    "examinations",
    "finances",
    "clinical_procedures",
]

EXPECTED_TABLE_VOLUMES = {
    "appointments": 55000,
    "clinical_charge_lines": 50001,
    "patients": 2002,
    "examinations": 1,
    "finances": 1,
    "clinical_procedures": 0,
}

# Relevant test, harness, fixture, runner, preflight, and runtime source files
RELEVANT_SOURCE_FILES = [
    "scripts/capture_pr3_staging_evidence.py",
    "tests/_support/pr3_lock_order_worker.php",
    "app/Services/AppointmentSchedulingService.php",
    "app/Services/AppointmentReschedulabilityService.php",
    "app/Services/AppointmentSlotEditContext.php",
    "app/Services/ClinicScheduleAvailabilityService.php",
    "app/Services/SchedulingAuthorityLocks.php",
    "app/Services/ClinicSchedulingSettings.php",
    "system/Test/bootstrap.php",
    "app/Config/Database.php",
    "app/Config/Routes.php",
    "phpunit.staging-certification.xml",
    "tests/unit/PR3StagingCertificationTest.php",
    "tests/unit/PR3StagingConcurrencyTest.php",
    "tests/unit/PR3StagingRollbackTest.php",
    "tests/unit/PR3IndependentTemporalTest.php",
    "tests/unit/ScheduleImpactWorkflowTest.php",
    "tests/unit/ScheduleImpactIndependentReviewTest.php",
    "tests/unit/ScheduleImpactUiRenderingTest.php",
    "tests/unit/PR2SlotAdviceReviewTest.php",
    "tests/_support/PhaseBFixture.php",
    "tests/js/ScheduleImpactMobileTest.cjs",
    "scripts/safe_test_runner.php",
    "scripts/TestDatabasePreflight.php",
    "app/Services/ScheduleImpactService.php",
    "app/Services/ClinicSchedulingConfigurationService.php",
    "app/Services/ScheduleImpactAuditStore.php",
    "app/Services/ProviderDirectoryService.php",
    "app/Services/ProviderScheduleService.php",
    "app/Services/ClinicTemporalPolicyService.php",
]

CERTIFICATION_ROLE = "Codex-executed"
CERTIFICATION_STATEMENT = (
    "Measurements recorded from local staging execution by Codex. "
    "Reviewer static approval does not constitute executed certification."
)
DEFAULT_TARGET_PR_SHA = "ad481ebbf454ebf3a53bc09417d13a2b2f57c0d5"



EXPECTED_COMMAND_ARGS = {
 'final-php': ['scripts/safe_test_runner.php', '-c', 'phpunit.staging-certification.xml', '--no-coverage', '--no-logging', '--do-not-cache-result'],
 'final-mobile': ['tests/js/ScheduleImpactMobileTest.cjs'],
}
def require(condition, message):
    if not condition:
        raise ValidationError(message)
def plain_path(path):
    for part in (path, *path.parents):
        if part.exists():
            attrs = part.lstat().st_file_attributes if hasattr(part.lstat(), 'st_file_attributes') else 0
            require(not part.is_symlink() and not attrs & getattr(stat, 'FILE_ATTRIBUTE_REPARSE_POINT', 0), 'Reparse/symlink path refused')
def valid_sha(value):
    return isinstance(value, str) and re.fullmatch(r'[a-f0-9]{64}', value) is not None
def timestamp(value):
    require(isinstance(value, str), 'Timestamp is required')
    try:
        result = datetime.fromisoformat(value.replace('Z', '+00:00'))
    except ValueError as exc:
        raise ValidationError('Invalid timestamp') from exc
    require(result.utcoffset() is not None, 'Timestamp must include timezone')
    return result


class ValidationError(Exception):
    """Raised when evidence validation or path safety check fails."""
    pass


# ----------------------------------------------------------------------
# Hashing & Source Normalization
# ----------------------------------------------------------------------

def sha256_bytes(data: bytes) -> str:
    """Compute hex SHA-256 digest of raw bytes."""
    return hashlib.sha256(data).hexdigest()


def sha256_file(filepath: Path) -> str:
    """Compute hex SHA-256 digest of an on-disk file."""
    h = hashlib.sha256()
    with open(filepath, "rb") as f:
        while chunk := f.read(65536):
            h.update(chunk)
    return h.hexdigest()


def canonical_lf_sha256(text: str) -> str:
    """Compute SHA-256 over LF-normalized UTF-8 source string."""
    normalized = text.replace("\r\n", "\n").replace("\r", "\n")
    return hashlib.sha256(normalized.encode("utf-8")).hexdigest()


def canonical_source_sha256(filepath: Path) -> str:
    """Read source file, normalize to LF UTF-8, and compute SHA-256."""
    if not filepath.is_file():
        raise ValidationError(f"Required source file missing: {filepath}")
    text = filepath.read_text(encoding="utf-8", errors="strict")
    return canonical_lf_sha256(text)


def compute_all_source_digests(repo_root: Path) -> Dict[str, str]:
    """Compute canonical LF SHA-256 for all allowlisted relevant source files."""
    digests: Dict[str, str] = {}
    for rel_path in sorted(RELEVANT_SOURCE_FILES):
        target = repo_root / rel_path
        plain_path(target)
        if not target.is_file():
            raise ValidationError(f"Required source file missing in repository: {rel_path}")
        digests[rel_path] = canonical_source_sha256(target)
    return digests


# ----------------------------------------------------------------------
# Log Sanitization & Redaction
# ----------------------------------------------------------------------

def sanitize_log_text(text: str, workspace_root: Optional[str] = None) -> str:
    """Sanitize log text by normalizing to LF and redacting local paths/passwords."""
    # 1. Normalize line endings to LF
    text = text.replace("\r\n", "\n").replace("\r", "\n")

    # 2. Redact workspace root if provided
    if workspace_root:
        norm_ws = os.path.normpath(workspace_root).replace("\\", "/")
        text = text.replace(workspace_root, "[WORKSPACE_ROOT]")
        text = text.replace(norm_ws, "[WORKSPACE_ROOT]")
        text = text.replace(norm_ws.replace("/", "\\"), "[WORKSPACE_ROOT]")

    # 3. Redact user paths (Windows and Unix)
    text = re.sub(
        r"[A-Za-z]:[\\/](?:Users|Documents and Settings)[\\/][A-Za-z0-9_.-]+",
        "[REDACTED_USER_PATH]",
        text,
    )
    text = re.sub(r"/(?:home|Users)/[A-Za-z0-9_.-]+", "[REDACTED_USER_PATH]", text)

    # 4. Redact XAMPP paths
    text = re.sub(r"[A-Za-z]:[\\/]xampp", "[XAMPP_ROOT]", text, flags=re.IGNORECASE)

    text = re.sub(r"(?i)\bBearer\s+[A-Za-z0-9._~+/=-]+", "Bearer [REDACTED]", text)

    # 5. Redact passwords and credentials in command-lines or logs
    text = re.sub(
        r"(?i)(password|passwd|pwd|secret|api[_-]?key|auth[_-]?token|bearer)\s*[:=]\s*[\"']?[^\s\"'&,;]+[\"']?",
        r"\1=[REDACTED]",
        text,
    )

    if not text.endswith("\n"):
        text += "\n"

    return text


# ----------------------------------------------------------------------
# Strict Field Projection (Privacy Enforcement)
# ----------------------------------------------------------------------

def project_scale_json(raw: Dict[str, Any]) -> Dict[str, Any]:
    """
    Explicitly project safe metrics and plan structures from scale JSON.
    Excludes query_prefix, full SQL, canaries, review IDs, and actor/patient IDs.
    """
    fixture = raw.get("fixture", {})
    benchmark = raw.get("benchmark", {})
    summary = raw.get("report_summary", {})
    before_rows = raw.get("before_rows", {})
    after_rows = raw.get("after_rows", {})
    raw_explain = raw.get("explain", {})

    safe_before: Dict[str, Any] = {}
    safe_after: Dict[str, Any] = {}
    for tbl in CORE_TABLES:
        b_entry = before_rows.get(tbl, {})
        a_entry = after_rows.get(tbl, {})
        safe_before[tbl] = {
            "count": int(b_entry.get("count", 0)),
            "sha256": str(b_entry.get("sha256", "")),
        }
        safe_after[tbl] = {
            "count": int(a_entry.get("count", 0)),
            "sha256": str(a_entry.get("sha256", "")),
        }

    safe_explain: Dict[str, Any] = {}
    for tbl, exp_val in raw_explain.items():
        if tbl not in CORE_TABLES: continue
        if isinstance(exp_val, dict):
            plan_rows = exp_val.get("plan", [])
            safe_plan: List[Dict[str, Any]] = []
            if isinstance(plan_rows, list):
                for row in plan_rows:
                    if isinstance(row, dict):
                        safe_plan.append({
                            "id": str(row.get("id", "")),
                            "select_type": str(row.get("select_type", "")),
                            "table": str(row.get("table", "")),
                            "type": row.get("type"),
                            "possible_keys": row.get("possible_keys"),
                            "key": row.get("key"),
                            "key_len": row.get("key_len"),
                            "ref": row.get("ref"),
                            "rows": row.get("rows"),
                            "Extra": str(row.get("Extra", "")),
                        })
            safe_explain[tbl] = {
                "query_sha256": str(exp_val.get("query_sha256", "")),
                "plan": safe_plan,
            }

    return {
        "fixture": {
            "historical_appointments": int(fixture.get("historical_appointments", 0)),
            "future_appointments": int(fixture.get("future_appointments", 0)),
            "historical_charges": int(fixture.get("historical_charges", 0)),
            "encounter_charges": int(fixture.get("encounter_charges", 0)),
            "patients": int(fixture.get("patients", 0)),
            "providers": int(fixture.get("providers", 0)),
        },
        "benchmark": {
            "lock_elapsed_seconds": float(benchmark.get("lock_elapsed_seconds", 0.0)),
            "lock_query_count": int(benchmark.get("lock_query_count", 0)),
            "point_lock_complexity": "O(future_active_appointments)",
            "report_elapsed_seconds": float(benchmark.get("report_elapsed_seconds", 0.0)),
            "report_query_count": int(benchmark.get("report_query_count", 0)),
            "locked_preview_total_elapsed_seconds": float(benchmark.get("locked_preview_total_elapsed_seconds", 0.0)),
            "memory_before_bytes": int(benchmark.get("memory_before_bytes", 0)),
            "peak_memory_bytes": int(benchmark.get("peak_memory_bytes", 0)),
        },
        "report_summary": {
            "total_future_visits": int(summary.get("total_future_visits", 0)),
            "reported_items_count": int(summary.get("reported_items_count", 0)),
            "omitted_elapsed_count": int(summary.get("omitted_elapsed_count", 0)),
        },
        "before_rows": safe_before,
        "after_rows": safe_after,
        "explain": safe_explain,
    }


def project_charge_json(raw: Dict[str, Any]) -> Dict[str, Any]:
    """
    Explicitly project safe fields from charge assessment JSON.
    Excludes explain_query_prefix and raw statements.
    """
    plan_rows = raw.get("plan", [])
    safe_plan: List[Dict[str, Any]] = []
    if isinstance(plan_rows, list):
        for row in plan_rows:
            if isinstance(row, dict):
                safe_plan.append({
                    "id": str(row.get("id", "")),
                    "select_type": str(row.get("select_type", "")),
                    "table": str(row.get("table", "")),
                    "type": row.get("type"),
                    "possible_keys": row.get("possible_keys"),
                    "key": row.get("key"),
                    "key_len": row.get("key_len"),
                    "ref": row.get("ref"),
                    "rows": row.get("rows"),
                    "Extra": str(row.get("Extra", "")),
                })

    indexes = [str(x) for x in raw.get("indexes_present", []) if isinstance(x, str)]
    summary = raw.get("assessment_summary", {})
    safe_summary = {"limits": "EXPLAIN estimates only; no DDL or production capacity certification."}

    return {
        "table": "clinical_charge_lines",
        "indexes_present": indexes,
        "composite_clinic_appointment_index_present": bool(raw.get("composite_clinic_appointment_index_present", False)),
        "optimizer_selected_key": raw.get("optimizer_selected_key"),
        "plan_estimated_rows": raw.get("plan_estimated_rows"),
        "source": "current-run populated scale report query",
        "populated_charge_rows": int(raw.get("populated_charge_rows", 0)),
        "query_sha256": str(raw.get("query_sha256", "")),
        "plan": safe_plan,
        "assessment_summary": safe_summary,
    }


def project_concurrency_json(raw):
    result={}
    for name in ['shared_global_authority','disjoint_clinic_mutex_isolation']:
        if name not in raw: continue
        item=raw[name];workers=item.get('workers')
        require(isinstance(workers,list), 'Expected actual worker results')
        statuses={'success':0,'rejected':0,'process_errors':0}
        for worker in workers:
            if worker.get('exit_code') != 0: statuses['process_errors']+=1;continue
            response=json.loads(worker['stdout'])
            statuses['success' if response.get('success') is True else 'rejected']+=1
        elapsed=item['elapsed_seconds'];require(isinstance(elapsed,(int,float)) and math.isfinite(elapsed) and elapsed>=0,'Invalid worker timing')
        result[name]={'workers':len(workers),'elapsed_seconds':elapsed,'status_counts':statuses}
    return result


def project_rollback_json(raw):
    return {k: raw[k] for k in ['previous_hours_restored','original_review_preserved','appointment_unchanged'] if type(raw.get(k)) is bool}


# ----------------------------------------------------------------------
# Fail-Closed Validations
# ----------------------------------------------------------------------

def validate_execution_record(record, input_dir, repo_root=None):
    require(isinstance(record, dict) and record.get('schema_version') == 1, 'Invalid execution schema')
    require(record.get('target_pr_sha') == DEFAULT_TARGET_PR_SHA, 'Application SHA mismatch')
    steps = record.get('ordered_steps')
    require(isinstance(steps, list) and [x.get('name') for x in steps] == ['final-php', 'final-mobile'], 'Expected two ordered final commands')
    previous = None
    for step, filename, executable in zip(steps, ['staging-final-php.log','staging-final-mobile.log'], ['php','node']):
        require(type(step.get('exit_code')) is int and step['exit_code'] == 0, 'Command failed')
        argv = step.get('argv')
        require(isinstance(argv,list) and all(isinstance(x,str) for x in argv) and argv[1:] == EXPECTED_COMMAND_ARGS[step['name']], 'Command mismatch')
        require(argv[0].replace('\\','/').split('/')[-1].lower().removesuffix('.exe') == executable, 'Executable mismatch')
        start, end = timestamp(step.get('started_at')), timestamp(step.get('finished_at'))
        require(start <= end and (previous is None or previous <= start), 'Execution timestamps out of order')
        previous = end
        require(step.get('stdout_file') == filename, 'Command/log binding mismatch')
        path = input_dir / filename; plain_path(path)
        require(path.is_file() and valid_sha(step.get('output_sha256')) and sha256_file(path) == step['output_sha256'], 'Command log hash mismatch')
    required = {'pr3-staging-scale.json','pr3-charge-index-assessment.json'} | {n for n in OPTIONAL_ARTIFACT_NAMES if (input_dir/n).exists()}
    hashes = record.get('artifact_hashes')
    require(isinstance(hashes,dict) and set(hashes) == required, 'Artifact provenance set mismatch')
    for name, digest in hashes.items():
        path=input_dir/name;plain_path(path)
        require(path.is_file() and valid_sha(digest) and sha256_file(path)==digest, 'Artifact hash mismatch')
    before, after = record.get('source_before_hashes'), record.get('source_after_hashes')
    require(isinstance(before,dict) and set(before) == set(RELEVANT_SOURCE_FILES) and before == after and all(valid_sha(x) for x in before.values()), 'Incomplete or changed source provenance')
    if repo_root is not None:
        require(before == compute_all_source_digests(repo_root), 'Source differs from executed source')
    env=record.get('environment')
    require(isinstance(env,dict), 'Runtime environment missing')
    for key in ['php_version','mariadb_version']:
        require(isinstance(env.get(key),str) and re.fullmatch(r'[0-9]+(?:[.][0-9]+){1,3}(?:-[A-Za-z0-9.]+)?',env[key]), 'Invalid runtime version')


def validate_php_log(raw_log: str) -> None:
    """Validate PHPUnit log shows zero failures/skips and matches success regexp."""
    if re.search(r"\bFAILURES!", raw_log):
        raise ValidationError("PHP test log contains FAILURES!")
    if re.search(r"\bERRORS!", raw_log):
        raise ValidationError("PHP test log contains ERRORS!")
    if re.search(r"\bFatal error\b", raw_log, re.IGNORECASE):
        raise ValidationError("PHP test log contains fatal PHP runtime error!")
    if re.search(r"\bParse error\b", raw_log, re.IGNORECASE):
        raise ValidationError("PHP test log contains PHP parse error!")
    if re.search(r"(?i)warning|risky|skipped", raw_log):
        raise ValidationError("Warnings, skips or risky results are not accepted")
    if "No tests executed" in raw_log:
        raise ValidationError("PHP test log indicates no tests were executed.")
    if "OK, but some tests were skipped!" in raw_log:
        raise ValidationError("PHP test log indicates skipped tests; staging certification requires 0 skips.")

    match = re.search(r"OK \((\d+) tests?, (\d+) assertions?\)", raw_log)
    if not match:
        raise ValidationError("PHP test log missing standard passing summary: 'OK (N tests, M assertions)'.")
    tests_count = int(match.group(1))
    assertions_count = int(match.group(2))
    if tests_count < 1 or assertions_count < 1:
        raise ValidationError(
            f"PHP test log reported insufficient tests/assertions: {tests_count} tests, {assertions_count} assertions."
        )


def validate_mobile_log(raw_log: str) -> None:
    """Validate headless Chromium log shows passed assertions with zero errors."""
    if re.search(r"\bAssertionError\b", raw_log):
        raise ValidationError("Mobile test log contains AssertionError!")
    if re.search(r"\bUnhandledPromiseRejection\b", raw_log):
        raise ValidationError("Mobile test log contains UnhandledPromiseRejection!")
    if re.search(r"\bError:\b", raw_log):
        raise ValidationError("Mobile test log contains runtime Error!")

    match = re.search(r"Schedule impact real Chromium:\s*(\d+)\s*assertions passed", raw_log)
    if not match:
        raise ValidationError("Mobile test log missing 'Schedule impact real Chromium: N assertions passed'.")
    assertions_count = int(match.group(1))
    if assertions_count < 198:
        raise ValidationError(f"Mobile assertions count below required 198: {assertions_count}.")


def _validate_scale_metrics_base(data: Dict[str, Any]) -> None:
    """Validate scale metrics for six table records and volumes."""
    fixture = data.get("fixture", {})
    if fixture.get("historical_appointments", 0) < 50000:
        raise ValidationError("Scale metrics historical_appointments < 50000.")
    if fixture.get("future_appointments", 0) < 5000:
        raise ValidationError("Scale metrics future_appointments < 5000.")
    if fixture.get("historical_charges", 0) < 50000:
        raise ValidationError("Scale metrics historical_charges < 50000.")
    if fixture.get("patients", 0) < 2002:
        raise ValidationError("Scale metrics patients < 2002.")

    summary = data.get("report_summary", {})
    if summary.get("reported_items_count") != 4999:
        raise ValidationError(f"Reported active items count mismatch: {summary.get('reported_items_count')} != 4999.")

    # Finite timing checks
    benchmark = data.get("benchmark", {})
    total_elapsed = benchmark.get("locked_preview_total_elapsed_seconds", 0.0)
    if total_elapsed <= 0.0 or total_elapsed >= 60.0:
        raise ValidationError(f"Scale benchmark elapsed time out of finite budget: {total_elapsed}s.")

    # Insist on all 6 tables in before and after rows
    before = data.get("before_rows", {})
    after = data.get("after_rows", {})
    for tbl in CORE_TABLES:
        if tbl not in before or tbl not in after:
            raise ValidationError(f"Scale metrics missing required core table snapshot: '{tbl}'.")
        exp_count = EXPECTED_TABLE_VOLUMES[tbl]
        if before[tbl].get("count") != exp_count:
            raise ValidationError(
                f"Table '{tbl}' volume mismatch: expected {exp_count}, got {before[tbl].get('count')}."
            )
        if before[tbl] != after[tbl]:
            raise ValidationError(f"Table '{tbl}' mutated during benchmark: before != after.")

    explain = data.get("explain", {})
    if "clinical_charge_lines" not in explain:
        raise ValidationError("Scale metrics missing explain plan for clinical_charge_lines.")
    ccl_plan = explain["clinical_charge_lines"]
    if not ccl_plan.get("plan"):
        raise ValidationError("clinical_charge_lines explain plan is empty.")
    if not ccl_plan.get("query_sha256"):
        raise ValidationError("clinical_charge_lines explain query_sha256 missing.")


def _validate_charge_assessment_base(charge_data: Dict[str, Any], scale_data: Dict[str, Any]) -> None:
    """Validate charge assessment and enforce provenance with scale explain plan."""
    if charge_data.get("table") != "clinical_charge_lines":
        raise ValidationError(f"Unexpected table in charge assessment: {charge_data.get('table')}")
    if charge_data.get("populated_charge_rows", 0) < 50001:
        raise ValidationError("Charge assessment populated_charge_rows < 50001.")
    if not charge_data.get("plan"):
        raise ValidationError("Charge assessment plan is empty.")

    # Provenance match with scale plan
    scale_ccl = scale_data.get("explain", {}).get("clinical_charge_lines", {})
    if charge_data.get("query_sha256") != scale_ccl.get("query_sha256"):
        raise ValidationError("Plan provenance mismatch: charge query_sha256 != scale explain query_sha256.")

    # Plan rows must match scale explain plan rows
    if charge_data.get("plan") != scale_ccl.get("plan"):
        raise ValidationError("Plan provenance mismatch: charge plan rows != scale explain plan rows.")


# ----------------------------------------------------------------------
# Path Integrity & Safety Check
# ----------------------------------------------------------------------

def validate_path_integrity(input_dir: Path, output_dir: Path) -> None:
    """Refuse nonempty output directory, overlapping directories, and symlinks."""
    plain_path(input_dir); plain_path(output_dir)
    require(input_dir.is_dir(), "Input directory missing")
    in_res = input_dir.resolve()
    out_res = output_dir.resolve()

    if in_res == out_res:
        raise ValidationError(f"Input and output directories cannot be the same: {in_res}")
    if out_res in in_res.parents or in_res in out_res.parents:
        raise ValidationError("Input and output directories must not be parent/child of each other.")

    # Refuse non-empty output directory
    if output_dir.exists():
        contents = list(output_dir.iterdir())
        if contents:
            raise ValidationError(
                f"Output directory is not empty: {output_dir}. "
                "Capture requires an empty or non-existent destination directory."
            )

    # Reject symlinks in input directory
    for item in input_dir.iterdir():
        plain_path(item)
        if item.is_symlink():
            raise ValidationError(f"Symlink detected in input directory: {item}. Symlinks are prohibited.")


# ----------------------------------------------------------------------

def validate_scale_metrics(data):
    require(isinstance(data,dict), 'Scale JSON must be object')
    _validate_scale_metrics_base(data)
    for key in ['lock_elapsed_seconds','report_elapsed_seconds','locked_preview_total_elapsed_seconds']:
        value=data['benchmark'].get(key)
        require(type(value) in [int,float] and math.isfinite(value) and value>=0,'Nonfinite or invalid timing')
    require(data['report_summary'].get('total_future_visits')==4999 and data['report_summary'].get('omitted_elapsed_count')==1,'Temporal counts mismatch')
    for table in CORE_TABLES:
        require(valid_sha(data['before_rows'][table].get('sha256')), 'Invalid preservation hash')
    require(valid_sha(data['explain']['clinical_charge_lines'].get('query_sha256')), 'Invalid query hash')
def validate_charge_assessment(charge_data, scale_data):
    require(isinstance(charge_data,dict), 'Charge JSON must be object')
    _validate_charge_assessment_base(charge_data,scale_data)
    require(valid_sha(charge_data.get('query_sha256')), 'Invalid charge query hash')

# Main Capture Procedure
# ----------------------------------------------------------------------

def capture_evidence(
    input_dir: Path,
    output_dir: Path,
    repo_root: Path,
    target_pr_sha: str = DEFAULT_TARGET_PR_SHA,
) -> Dict[str, Any]:
    """
    Validate, sanitize, project, and bind execution evidence into output_dir.
    """
    require(target_pr_sha == DEFAULT_TARGET_PR_SHA, "Application SHA mismatch")
    validate_path_integrity(input_dir, output_dir)

    # 1. Read and validate root-produced execution-record.json
    exec_record_file = input_dir / "execution-record.json"
    if not exec_record_file.is_file():
        raise ValidationError(
            f"Missing required execution-record.json in {input_dir}. "
            "Evidence capture requires a root-produced execution record."
        )

    try:
        exec_record = json.loads(exec_record_file.read_text(encoding="utf-8"))
    except json.JSONDecodeError as e:
        raise ValidationError(f"Malformed execution-record.json: {e}")

    validate_execution_record(exec_record, input_dir=input_dir, repo_root=repo_root)

    # 2. Read raw logs and validate semantics
    raw_php_log = (input_dir / "staging-final-php.log").read_text(encoding="utf-8", errors="strict")
    validate_php_log(raw_php_log)

    raw_mobile_log = (input_dir / "staging-final-mobile.log").read_text(encoding="utf-8", errors="strict")
    validate_mobile_log(raw_mobile_log)

    # 3. Read raw metrics and validate data
    try:
        raw_scale = json.loads((input_dir / "pr3-staging-scale.json").read_text(encoding="utf-8"))
    except json.JSONDecodeError as e:
        raise ValidationError(f"Malformed pr3-staging-scale.json: {e}")
    validate_scale_metrics(raw_scale)

    try:
        raw_charge = json.loads((input_dir / "pr3-charge-index-assessment.json").read_text(encoding="utf-8"))
    except json.JSONDecodeError as e:
        raise ValidationError(f"Malformed pr3-charge-index-assessment.json: {e}")
    validate_charge_assessment(raw_charge, raw_scale)

    # Optional metrics
    optional_projected: Dict[str, Any] = {}
    if (input_dir / "pr3-staging-concurrency.json").is_file():
        try:
            raw_conc = json.loads((input_dir / "pr3-staging-concurrency.json").read_text(encoding="utf-8"))
            optional_projected["pr3-staging-concurrency.json"] = project_concurrency_json(raw_conc)
        except json.JSONDecodeError as e:
            raise ValidationError(f"Malformed pr3-staging-concurrency.json: {e}")

    if (input_dir / "pr3-staging-rollback.json").is_file():
        try:
            raw_rb = json.loads((input_dir / "pr3-staging-rollback.json").read_text(encoding="utf-8"))
            optional_projected["pr3-staging-rollback.json"] = project_rollback_json(raw_rb)
        except json.JSONDecodeError as e:
            raise ValidationError(f"Malformed pr3-staging-rollback.json: {e}")

    # 4. Create fresh output directory
    output_dir.mkdir(parents=True, exist_ok=True)
    ws_root = str(repo_root)

    # 5. Sanitize and write out logs
    clean_php_log = sanitize_log_text(raw_php_log, workspace_root=ws_root)
    (output_dir / "staging-final-php.log").write_text(clean_php_log, encoding="utf-8", newline="\n")

    clean_mobile_log = sanitize_log_text(raw_mobile_log, workspace_root=ws_root)
    (output_dir / "staging-final-mobile.log").write_text(clean_mobile_log, encoding="utf-8", newline="\n")

    # 6. Project and write out JSON artifacts (strictly safe schema)
    safe_scale = project_scale_json(raw_scale)
    (output_dir / "pr3-staging-scale.json").write_text(
        json.dumps(safe_scale, indent=2, sort_keys=True) + "\n",
        encoding="utf-8",
        newline="\n",
    )

    safe_charge = project_charge_json(raw_charge)
    (output_dir / "pr3-charge-index-assessment.json").write_text(
        json.dumps(safe_charge, indent=2, sort_keys=True) + "\n",
        encoding="utf-8",
        newline="\n",
    )

    for opt_name, opt_data in optional_projected.items():
        (output_dir / opt_name).write_text(
            json.dumps(opt_data, indent=2, sort_keys=True) + "\n",
            encoding="utf-8",
            newline="\n",
        )

    # 7. Write environment.json incorporating root execution metadata
    source_digests = compute_all_source_digests(repo_root)
    now_utc = datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")

    declared_env = exec_record.get("environment", {})
    env_data = {
        "certification_statement": CERTIFICATION_STATEMENT,
        "executor_role": CERTIFICATION_ROLE,
        "execution_record_steps": [
            {
                "name": s.get("name"),
                "argv": ["php" if s["name"] == "final-php" else "node"] + s["argv"][1:],
                "started_at": s["started_at"],
                "finished_at": s["finished_at"],
                "exit_code": s.get("exit_code"),
                "stdout_file": s.get("stdout_file"),
                "output_sha256": s.get("output_sha256"),
            }
            for s in exec_record.get("ordered_steps", [])
        ],
        "platform": {
            "machine": platform.machine(),
            "python_version": platform.python_version(),
            "release": platform.release(),
            "system": platform.system(),
        },
        "root_runtime_environment": {k: declared_env[k] for k in ["php_version","mariadb_version"]},
        "raw_execution_record_sha256": sha256_file(exec_record_file),
        "raw_metric_sha256": exec_record["artifact_hashes"],
        "source_hash_definition": "SHA256 of LF-normalized UTF8 source; execution/capture binding, not a signed attestation",
        "source_canonical_lf_digests": source_digests,
        "target_pr_sha": target_pr_sha,
        "timestamp_utc": now_utc,
    }
    (output_dir / "environment.json").write_text(
        json.dumps(env_data, indent=2, sort_keys=True) + "\n",
        encoding="utf-8",
        newline="\n",
    )

    # 8. Generate manifest.sha256 and manifest.json over exact fixed set
    target_artifacts = sorted(
        set(FIXED_ARTIFACT_NAMES) | set(optional_projected.keys())
    )
    manifest_artifacts: Dict[str, Dict[str, Any]] = {}
    manifest_lines: List[str] = []

    for name in target_artifacts:
        art_path = output_dir / name
        if not art_path.is_file():
            raise ValidationError(f"Expected generated artifact missing: {name}")
        art_sha = sha256_file(art_path)
        art_size = art_path.stat().st_size
        manifest_artifacts[name] = {"bytes": art_size, "sha256": art_sha}
        manifest_lines.append(f"{art_sha}  {name}")

    (output_dir / "manifest.sha256").write_text(
        "\n".join(manifest_lines) + "\n", encoding="utf-8", newline="\n"
    )

    manifest_json_data = {
        "artifacts": manifest_artifacts,
        "manifest_version": "1.0",
        "role": CERTIFICATION_ROLE,
        "source_canonical_lf_digests": source_digests,
        "target_pr_sha": target_pr_sha,
        "verified": True,
    }
    (output_dir / "manifest.json").write_text(
        json.dumps(manifest_json_data, indent=2, sort_keys=True) + "\n",
        encoding="utf-8",
        newline="\n",
    )

    return manifest_json_data


# ----------------------------------------------------------------------
# Verification Procedure
# ----------------------------------------------------------------------

def verify_evidence(
    evidence_dir: Path,
    repo_root: Optional[Path] = None,
    verify_source: bool = False,
) -> bool:
    """
    Verify captured evidence against manifest.sha256 and manifest.json.
    Enforces exact fixed allowlist, zero unmanifested files, and provenance.
    """
    if not evidence_dir.is_dir():
        raise ValidationError(f"Evidence directory does not exist: {evidence_dir}")

    manifest_json_file = evidence_dir / "manifest.json"
    manifest_sha_file = evidence_dir / "manifest.sha256"

    if not manifest_json_file.is_file():
        raise ValidationError(f"Missing manifest.json in {evidence_dir}")
    if not manifest_sha_file.is_file():
        raise ValidationError(f"Missing manifest.sha256 in {evidence_dir}")

    try:
        manifest_data = json.loads(manifest_json_file.read_text(encoding="utf-8"))
    except json.JSONDecodeError as e:
        raise ValidationError(f"Malformed manifest.json: {e}")

    artifacts: Dict[str, Dict[str, Any]] = manifest_data.get("artifacts", {})
    if not artifacts:
        raise ValidationError("Manifest contains no registered artifacts.")

    # Parse manifest.sha256
    sha256_map: Dict[str, str] = {}
    for line in manifest_sha_file.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line:
            continue
        parts = line.split(maxsplit=1)
        if len(parts) == 2:
            sha256_map[parts[1]] = parts[0]

    required=set(FIXED_ARTIFACT_NAMES)
    permitted=required | set(OPTIONAL_ARTIFACT_NAMES)
    require(required <= set(artifacts) <= permitted, 'Manifest required/allowed artifact set mismatch')
    plain_path(evidence_dir)
    # Agreement check: manifest.json and manifest.sha256 must register exact same set
    if set(artifacts.keys()) != set(sha256_map.keys()):
        raise ValidationError(
            f"Manifest mismatch: manifest.json keys ({sorted(artifacts.keys())}) "
            f"!= manifest.sha256 keys ({sorted(sha256_map.keys())})."
        )

    # Check for path traversal or symlinks in registered names
    for name in artifacts:
        if "/" in name or "\\" in name or ".." in name:
            raise ValidationError(f"Path traversal detected in artifact name: {name}")

    # Verify each artifact against hashes
    for filename, meta in artifacts.items():
        target = evidence_dir / filename
        plain_path(target)
        if not target.is_file() or target.is_symlink():
            raise ValidationError(f"Manifest artifact missing or is symlink: {target}")

        actual_sha = sha256_file(target)
        if actual_sha != meta.get("sha256"):
            raise ValidationError(
                f"SHA-256 mismatch for {filename}: expected {meta.get('sha256')}, got {actual_sha}."
            )
        if actual_sha != sha256_map[filename]:
            raise ValidationError(
                f"manifest.sha256 mismatch for {filename}: expected {sha256_map[filename]}, got {actual_sha}."
            )

    # Verify zero unmanifested files in evidence_dir
    allowed_names = set(artifacts.keys()) | {"manifest.json", "manifest.sha256"}
    for item in evidence_dir.iterdir():
        if item.name not in allowed_names or not item.is_file():
            raise ValidationError(
                f"Unmanifested file found in evidence directory: {item.name}. "
                "All files in evidence directory must be explicitly manifested."
            )

    # Validate projected data semantics
    php_log = (evidence_dir / "staging-final-php.log").read_text(encoding="utf-8", errors="strict")
    validate_php_log(php_log)

    mobile_log = (evidence_dir / "staging-final-mobile.log").read_text(encoding="utf-8", errors="strict")
    validate_mobile_log(mobile_log)

    scale_data = json.loads((evidence_dir / "pr3-staging-scale.json").read_text(encoding="utf-8"))
    validate_scale_metrics(scale_data)

    charge_data = json.loads((evidence_dir / "pr3-charge-index-assessment.json").read_text(encoding="utf-8"))
    validate_charge_assessment(charge_data, scale_data)

    environment=json.loads((evidence_dir/'environment.json').read_text(encoding='utf8'))
    recorded=manifest_data.get('source_canonical_lf_digests')
    require(isinstance(recorded,dict) and set(recorded)==set(RELEVANT_SOURCE_FILES) and all(valid_sha(v) for v in recorded.values()),'Incomplete source manifest')
    require(environment.get('source_canonical_lf_digests') == recorded, 'Environment/source manifest mismatch')
    require(environment.get('target_pr_sha') == manifest_data.get('target_pr_sha') == DEFAULT_TARGET_PR_SHA, 'Application SHA metadata mismatch')
    require(valid_sha(environment.get('raw_execution_record_sha256')), 'Missing execution record digest')
    expected_metrics={'pr3-staging-scale.json','pr3-charge-index-assessment.json'} | (set(artifacts) & set(OPTIONAL_ARTIFACT_NAMES))
    raw_metrics=environment.get('raw_metric_sha256')
    require(isinstance(raw_metrics,dict) and set(raw_metrics)==expected_metrics and all(valid_sha(v) for v in raw_metrics.values()), 'Incomplete metric provenance')
    runtime=environment.get('root_runtime_environment')
    require(isinstance(runtime,dict) and set(runtime)=={'php_version','mariadb_version'}, 'Missing runtime metadata')
    for value in runtime.values():
        require(isinstance(value,str) and re.fullmatch(r'[0-9]+(?:[.][0-9]+){1,3}(?:-[A-Za-z0-9.]+)?',value),'Invalid runtime metadata')
    steps=environment.get('execution_record_steps')
    require(isinstance(steps,list) and [x.get('name') for x in steps]==['final-php','final-mobile'],'Missing ordered execution metadata')
    previous=None
    for step,filename,executable in zip(steps,['staging-final-php.log','staging-final-mobile.log'],['php','node']):
        require(step.get('argv') == [executable]+EXPECTED_COMMAND_ARGS[step['name']], 'Published command mismatch')
        require(type(step.get('exit_code')) is int and step['exit_code']==0 and step.get('stdout_file')==filename and valid_sha(step.get('output_sha256')), 'Published command binding mismatch')
        start,end=timestamp(step.get('started_at')),timestamp(step.get('finished_at'))
        require(start<=end and (previous is None or previous<=start),'Published timestamps out of order')
        previous=end

    # Verify source integrity if --verify-source is requested
    if verify_source:
        if not repo_root or not repo_root.is_dir():
            raise ValidationError("--verify-source requested but repository root not provided or invalid.")
        recorded_digests = manifest_data.get("source_canonical_lf_digests", {})
        current_digests = compute_all_source_digests(repo_root)
        for rel_file, rec_hash in recorded_digests.items():
            curr_hash = current_digests.get(rel_file)
            if curr_hash != rec_hash:
                raise ValidationError(
                    f"Source integrity verification failed for '{rel_file}': "
                    f"recorded {rec_hash}, current {curr_hash}."
                )

    return True


# ----------------------------------------------------------------------
# Built-In Self-Tests
# ----------------------------------------------------------------------

class TestCaptureCorrectionSuite(unittest.TestCase):
    def setUp(self) -> None:
        import tempfile
        self.temp_dir = tempfile.TemporaryDirectory()
        self.tmp_path = Path(self.temp_dir.name)
        self.input_dir = self.tmp_path / "input"
        self.input_dir.mkdir()
        self.output_dir = self.tmp_path / "output"
        self.repo_dir = self.tmp_path / "repo"
        self.repo_dir.mkdir()

        # Create mock source files in mock repo
        for rel_path in RELEVANT_SOURCE_FILES:
            target = self.repo_dir / rel_path
            target.parent.mkdir(parents=True, exist_ok=True)
            target.write_text(f"// Content for {rel_path}\n", encoding="utf-8")

    def tearDown(self) -> None:
        self.temp_dir.cleanup()

    def test_execution_bindings_fail_closed(self):
        self._create_valid_fixtures()
        record=json.loads((self.input_dir/'execution-record.json').read_text())
        import copy
        variants=[]
        for field,value in [('source_before_hashes', {}), ('artifact_hashes', {})]:
            bad=copy.deepcopy(record);bad[field]=value;variants.append(bad)
        for key,value in [('stdout_file','../outside.log'), ('argv',['php','unexpected.php']), ('started_at',None), ('exit_code',True)]:
            bad=copy.deepcopy(record);bad['ordered_steps'][0][key]=value;variants.append(bad)
        bad=copy.deepcopy(record);name=next(iter(bad['source_before_hashes']));bad['source_before_hashes'].pop(name);bad['source_after_hashes'].pop(name);variants.append(bad)
        for bad in variants:
            with self.subTest(record=bad):
                with self.assertRaises(ValidationError):validate_execution_record(bad,self.input_dir,self.repo_dir)

    def test_nonfinite_timing_and_invalid_digest_rejected(self):
        self._create_valid_fixtures()
        data=json.loads((self.input_dir/'pr3-staging-scale.json').read_text())
        import copy
        for value in [float('nan'),float('inf')]:
            bad=copy.deepcopy(data);bad['benchmark']['locked_preview_total_elapsed_seconds']=value
            with self.assertRaises(ValidationError):validate_scale_metrics(bad)
        data['before_rows']['patients']['sha256']='short'
        with self.assertRaises(ValidationError):validate_scale_metrics(data)

    def test_missing_environment_in_both_manifests_rejected(self):
        self._create_valid_fixtures();capture_evidence(self.input_dir,self.output_dir,self.repo_dir)
        path=self.output_dir/'manifest.json';manifest=json.loads(path.read_text());manifest['artifacts'].pop('environment.json');path.write_text(json.dumps(manifest))
        path=self.output_dir/'manifest.sha256';path.write_text(''.join(line for line in path.read_text().splitlines(keepends=True) if not line.rstrip().endswith('environment.json')))
        (self.output_dir/'environment.json').unlink()
        with self.assertRaises(ValidationError):verify_evidence(self.output_dir)

    def test_optional_summaries_remove_raw_identity_fields(self):
        raw={'shared_global_authority':{'elapsed_seconds':0.5,'workers':[{'exit_code':0,'actor_id':99,'stdout':'{"success":true,"patient_id":99}'}]}}
        summary=project_concurrency_json(raw)
        self.assertEqual(summary['shared_global_authority']['workers'],1)
        self.assertNotIn('99',json.dumps(summary))
        self.assertEqual(project_rollback_json({'actor_id':True,'scope':'private','appointment_unchanged':True}),{'appointment_unchanged':True})

    def test_rehashed_incomplete_execution_metadata_rejected(self):
        self._create_valid_fixtures();capture_evidence(self.input_dir,self.output_dir,self.repo_dir)
        path=self.output_dir/'environment.json';environment=json.loads(path.read_text());environment.pop('execution_record_steps');path.write_bytes(json.dumps(environment).encode())
        digest=sha256_file(path)
        path=self.output_dir/'manifest.json';manifest=json.loads(path.read_text());manifest['artifacts']['environment.json']['sha256']=digest;path.write_text(json.dumps(manifest))
        path=self.output_dir/'manifest.sha256';path.write_text(''.join((digest+'  environment.json\n') if line.rstrip().endswith('environment.json') else line for line in path.read_text().splitlines(keepends=True)))
        with self.assertRaises(ValidationError):verify_evidence(self.output_dir)
        self.assertNotIn('PRIVATE_TOKEN',sanitize_log_text('Authorization: Bearer PRIVATE_TOKEN'))

    def _create_valid_fixtures(self) -> None:
        php_log = (
            "PHPUnit 10.5.0 by Sebastian Bergmann and contributors.\n"
            "Testing Tests\\Unit\\PR3StagingCertificationTest\n"
            "....                                                                4 / 4 (100%)\n"
            "Time: 00:26.683, Memory: 64.00 MB\n"
            "OK (41 tests, 643 assertions)\n"
        )
        php_bytes = php_log.encode("utf-8")
        (self.input_dir / "staging-final-php.log").write_bytes(php_bytes)
        php_sha = sha256_bytes(php_bytes)

        mobile_log = (
            "Schedule impact real Chromium: 198 assertions passed "
            "(actual EN/AR preview/recheck at 320/360/390px; 505 affected visits and compact submit JSON).\n"
        )
        mobile_bytes = mobile_log.encode("utf-8")
        (self.input_dir / "staging-final-mobile.log").write_bytes(mobile_bytes)
        mobile_sha = sha256_bytes(mobile_bytes)

        query_sha = hashlib.sha256(b"SELECT DISTINCT appointment_id FROM clinical_charge_lines").hexdigest()
        plan_rows = [{"id": "1", "select_type": "PRIMARY", "table": "clinical_charge_lines", "key": "idx_ccl_patient_status"}]

        scale_obj = {
            "fixture": {
                "historical_appointments": 50000,
                "future_appointments": 5000,
                "historical_charges": 50000,
                "encounter_charges": 1,
                "patients": 2002,
                "providers": 4,
            },
            "benchmark": {
                "lock_elapsed_seconds": 0.38,
                "lock_query_count": 5014,
                "report_elapsed_seconds": 0.56,
                "report_query_count": 43,
                "locked_preview_total_elapsed_seconds": 0.95,
                "memory_before_bytes": 25000000,
                "peak_memory_bytes": 62000000,
            },
            "report_summary": {
                "total_future_visits": 4999,
                "reported_items_count": 4999,
                "omitted_elapsed_count": 1,
            },
            "before_rows": {
                "appointments": {"count": 55000, "sha256": sha256_bytes(b"h_app")},
                "clinical_charge_lines": {"count": 50001, "sha256": sha256_bytes(b"h_ccl")},
                "patients": {"count": 2002, "sha256": sha256_bytes(b"h_pat")},
                "examinations": {"count": 1, "sha256": sha256_bytes(b"h_exam")},
                "finances": {"count": 1, "sha256": sha256_bytes(b"h_fin")},
                "clinical_procedures": {"count": 0, "sha256": sha256_bytes(b"h_proc")},
            },
            "after_rows": {
                "appointments": {"count": 55000, "sha256": sha256_bytes(b"h_app")},
                "clinical_charge_lines": {"count": 50001, "sha256": sha256_bytes(b"h_ccl")},
                "patients": {"count": 2002, "sha256": sha256_bytes(b"h_pat")},
                "examinations": {"count": 1, "sha256": sha256_bytes(b"h_exam")},
                "finances": {"count": 1, "sha256": sha256_bytes(b"h_fin")},
                "clinical_procedures": {"count": 0, "sha256": sha256_bytes(b"h_proc")},
            },
            "explain": {
                "clinical_charge_lines": {
                    "query_sha256": query_sha,
                    "query_prefix": "SELECT DISTINCT appointment_id FROM clinical_charge_lines WHERE clinic_id = 999",
                    "plan": plan_rows,
                }
            },
            "canaries": {"actor_user_id": 999, "patient_name": "Test Patient", "arbitrary_field": "injected"},
        }
        scale_bytes = (json.dumps(scale_obj, indent=2) + "\n").encode("utf-8")
        (self.input_dir / "pr3-staging-scale.json").write_bytes(scale_bytes)
        scale_sha = sha256_bytes(scale_bytes)

        charge_obj = {
            "table": "clinical_charge_lines",
            "indexes_present": ["PRIMARY", "idx_ccl_patient_status"],
            "composite_clinic_appointment_index_present": False,
            "optimizer_selected_key": "idx_ccl_patient_status",
            "plan_estimated_rows": "25001",
            "source": "current-run populated scale report query",
            "populated_charge_rows": 50001,
            "query_sha256": query_sha,
            "explain_query_prefix": "SELECT DISTINCT appointment_id FROM clinical_charge_lines WHERE clinic_id = 999",
            "plan": plan_rows,
            "assessment_summary": {"current_behavior": "scans charges"},
            "arbitrary_field": "injected",
        }
        charge_bytes = (json.dumps(charge_obj, indent=2) + "\n").encode("utf-8")
        (self.input_dir / "pr3-charge-index-assessment.json").write_bytes(charge_bytes)
        charge_sha = sha256_bytes(charge_bytes)

        # Build mock execution-record.json
        src_digests = compute_all_source_digests(self.repo_dir)
        exec_record = {
            "schema_version": 1,
            "target_pr_sha": DEFAULT_TARGET_PR_SHA,
            "environment": {"php_version": "8.2.12", "mariadb_version": "10.4.32"},
            "ordered_steps": [
                {
                    "name": "final-php",
                    "argv": ["php"] + EXPECTED_COMMAND_ARGS["final-php"],
                    "started_at": "2030-01-01T00:00:00Z",
                    "finished_at": "2030-01-01T00:00:01Z",
                    "exit_code": 0,
                    "stdout_file": "staging-final-php.log",
                    "output_sha256": php_sha,
                },
                {
                    "name": "final-mobile",
                    "argv": ["node"] + EXPECTED_COMMAND_ARGS["final-mobile"],
                    "started_at": "2030-01-01T00:00:02Z",
                    "finished_at": "2030-01-01T00:00:03Z",
                    "exit_code": 0,
                    "stdout_file": "staging-final-mobile.log",
                    "output_sha256": mobile_sha,
                },
            ],
            "source_before_hashes": src_digests,
            "source_after_hashes": src_digests,
            "artifact_hashes": {
                "pr3-staging-scale.json": scale_sha,
                "pr3-charge-index-assessment.json": charge_sha,
            },
        }
        (self.input_dir / "execution-record.json").write_bytes(json.dumps(exec_record).encode("utf-8"))

    def test_injected_actor_ids_and_arbitrary_fields_excluded(self) -> None:
        self._create_valid_fixtures()
        capture_evidence(self.input_dir, self.output_dir, self.repo_dir)

        captured_scale = json.loads((self.output_dir / "pr3-staging-scale.json").read_text(encoding="utf-8"))
        self.assertNotIn("canaries", captured_scale)
        self.assertNotIn("arbitrary_field", captured_scale)
        self.assertNotIn("query_prefix", captured_scale["explain"]["clinical_charge_lines"])

        captured_charge = json.loads((self.output_dir / "pr3-charge-index-assessment.json").read_text(encoding="utf-8"))
        self.assertNotIn("arbitrary_field", captured_charge)
        self.assertNotIn("explain_query_prefix", captured_charge)

    def test_text_sanitizer(self) -> None:
        raw = "User path C:\\Users\\Administrator\\workspace and C:/xampp/htdocs/dental-saas with password=secret123"
        sanitized = sanitize_log_text(raw, workspace_root="C:\\xampp\\htdocs\\dental-saas")
        self.assertNotIn("Administrator", sanitized)
        self.assertNotIn("secret123", sanitized)
        self.assertIn("[REDACTED_USER_PATH]", sanitized)
        self.assertIn("password=[REDACTED]", sanitized)

    def test_preexisting_output_file_rejected(self) -> None:
        self._create_valid_fixtures()
        self.output_dir.mkdir()
        (self.output_dir / "stray.txt").write_text("pre-existing")
        with self.assertRaises(ValidationError) as ctx:
            capture_evidence(self.input_dir, self.output_dir, self.repo_dir)
        self.assertIn("Output directory is not empty", str(ctx.exception))

    def test_path_traversal_and_symlink_rejected(self) -> None:
        self._create_valid_fixtures()
        # Same directory rejected
        with self.assertRaises(ValidationError) as ctx:
            capture_evidence(self.input_dir, self.input_dir, self.repo_dir)
        self.assertIn("Input and output directories cannot be the same", str(ctx.exception))

        # Nested directory rejected
        with self.assertRaises(ValidationError) as ctx:
            capture_evidence(self.input_dir, self.input_dir / "nested", self.repo_dir)
        self.assertIn("parent/child", str(ctx.exception))

    def test_source_missing_fails(self) -> None:
        # Delete one required source file
        (self.repo_dir / "phpunit.staging-certification.xml").unlink()
        with self.assertRaises(ValidationError) as ctx:
            compute_all_source_digests(self.repo_dir)
        self.assertIn("Required source file missing", str(ctx.exception))

    def test_source_drift_fails_when_verify_source(self) -> None:
        self._create_valid_fixtures()
        capture_evidence(self.input_dir, self.output_dir, self.repo_dir)

        # Mutate a source file
        target = self.repo_dir / "tests/unit/PR3StagingCertificationTest.php"
        target.write_text("// Drifted content\n", encoding="utf-8")

        with self.assertRaises(ValidationError) as ctx:
            verify_evidence(self.output_dir, self.repo_dir, verify_source=True)
        self.assertIn("Source integrity verification failed", str(ctx.exception))

    def test_canonical_lf_hash_newline_invariant(self) -> None:
        crlf = "line 1\r\nline 2\r\n"
        lf = "line 1\nline 2\n"
        self.assertEqual(canonical_lf_sha256(crlf), canonical_lf_sha256(lf))

    def test_manifest_disagreement_fails(self) -> None:
        self._create_valid_fixtures()
        capture_evidence(self.input_dir, self.output_dir, self.repo_dir)

        # Alter manifest.sha256 by removing one file
        lines = (self.output_dir / "manifest.sha256").read_text(encoding="utf-8").splitlines()
        (self.output_dir / "manifest.sha256").write_text("\n".join(lines[:-1]) + "\n", encoding="utf-8")

        with self.assertRaises(ValidationError) as ctx:
            verify_evidence(self.output_dir, self.repo_dir)
        self.assertIn("Manifest mismatch", str(ctx.exception))

    def test_old_source_log_metrics_mismatch(self) -> None:
        self._create_valid_fixtures()
        # Alter execution record artifact hash to simulate stale metrics
        rec = json.loads((self.input_dir / "execution-record.json").read_text(encoding="utf-8"))
        rec["artifact_hashes"]["pr3-staging-scale.json"] = "stale_hash_from_old_run"
        (self.input_dir / "execution-record.json").write_text(json.dumps(rec), encoding="utf-8")

        with self.assertRaises(ValidationError) as ctx:
            capture_evidence(self.input_dir, self.output_dir, self.repo_dir)
        self.assertIn("Artifact hash mismatch", str(ctx.exception))

    def test_malformed_success_marker_fails(self) -> None:
        self._create_valid_fixtures()
        # Malform PHP log to show 0 assertions
        bad_log = "OK (4 tests, 0 assertions)\n"
        bad_bytes = bad_log.encode("utf-8")
        (self.input_dir / "staging-final-php.log").write_bytes(bad_bytes)
        rec = json.loads((self.input_dir / "execution-record.json").read_text(encoding="utf-8"))
        rec["ordered_steps"][0]["output_sha256"] = sha256_bytes(bad_bytes)
        (self.input_dir / "execution-record.json").write_bytes(json.dumps(rec).encode("utf-8"))

        with self.assertRaises(ValidationError) as ctx:
            capture_evidence(self.input_dir, self.output_dir, self.repo_dir)
        self.assertIn("insufficient tests/assertions", str(ctx.exception))

    def test_six_table_volumes_enforced(self) -> None:
        self._create_valid_fixtures()
        scale = json.loads((self.input_dir / "pr3-staging-scale.json").read_text(encoding="utf-8"))
        del scale["before_rows"]["clinical_procedures"]
        scale_bytes = (json.dumps(scale, indent=2) + "\n").encode("utf-8")
        (self.input_dir / "pr3-staging-scale.json").write_bytes(scale_bytes)
        rec = json.loads((self.input_dir / "execution-record.json").read_text(encoding="utf-8"))
        rec["artifact_hashes"]["pr3-staging-scale.json"] = sha256_bytes(scale_bytes)
        (self.input_dir / "execution-record.json").write_bytes(json.dumps(rec).encode("utf-8"))

        with self.assertRaises(ValidationError) as ctx:
            capture_evidence(self.input_dir, self.output_dir, self.repo_dir)
        self.assertIn("missing required core table snapshot: 'clinical_procedures'", str(ctx.exception))

    def test_end_to_end_capture_and_verify(self) -> None:
        self._create_valid_fixtures()
        manifest = capture_evidence(self.input_dir, self.output_dir, self.repo_dir)
        self.assertTrue(manifest.get("verified"))
        self.assertTrue((self.output_dir / "manifest.sha256").is_file())
        self.assertTrue((self.output_dir / "manifest.json").is_file())

        # Verify output directory with source verification enabled
        ok = verify_evidence(self.output_dir, self.repo_dir, verify_source=True)
        self.assertTrue(ok)


def run_self_tests() -> int:
    suite = unittest.defaultTestLoader.loadTestsFromTestCase(TestCaptureCorrectionSuite)
    runner = unittest.TextTestRunner(verbosity=2)
    result = runner.run(suite)
    return 0 if result.wasSuccessful() else 1


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Capture, sanitize, project, and verify PR3/PR4 staging certification execution evidence."
    )
    subparsers = parser.add_subparsers(dest="command", help="Command to run")

    # capture subcommand
    capture_parser = subparsers.add_parser("capture", help="Capture and sanitize evidence from local test runs.")
    capture_parser.add_argument(
        "--input-dir",
        type=Path,
        default=Path("tmp"),
        help="Directory containing execution-record.json, test logs, and raw artifacts (default: tmp).",
    )
    capture_parser.add_argument(
        "--output-dir",
        type=Path,
        default=Path("docs/SaaS/evidence/staging_pr3"),
        help="Destination directory (must be empty or newly created, default: docs/SaaS/evidence/staging_pr3).",
    )
    capture_parser.add_argument(
        "--app-sha",
        type=str,
        default=DEFAULT_TARGET_PR_SHA,
        help=f"Target PR commit SHA (default: {DEFAULT_TARGET_PR_SHA}).",
    )

    # verify subcommand
    verify_parser = subparsers.add_parser("verify", help="Verify integrity of captured evidence against manifests.")
    verify_parser.add_argument(
        "--evidence-dir",
        type=Path,
        default=Path("docs/SaaS/evidence/staging_pr3"),
        help="Evidence directory to verify (default: docs/SaaS/evidence/staging_pr3).",
    )
    verify_parser.add_argument(
        "--verify-source",
        action="store_true",
        help="Verify canonical LF source hashes of current repository match declared evidence digests.",
    )

    # test flag
    parser.add_argument(
        "--test",
        action="store_true",
        help="Run built-in tool self-tests.",
    )

    args = parser.parse_args()

    if args.test or args.command == "test":
        return run_self_tests()

    repo_root = Path.cwd().resolve()

    if args.command == "capture":
        input_dir = Path(os.path.abspath(args.input_dir))
        output_dir = Path(os.path.abspath(args.output_dir))
        try:
            print(f"[INFO] Capturing evidence from {input_dir} -> {output_dir}...")
            manifest = capture_evidence(
                input_dir=input_dir,
                output_dir=output_dir,
                repo_root=repo_root,
                target_pr_sha=args.app_sha,
            )
            print(f"[OK] Captured and bound {len(manifest['artifacts'])} artifacts successfully.")
            print(f"[OK] Manifest generated: {output_dir / 'manifest.json'}")
            print(f"[OK] Checksums generated: {output_dir / 'manifest.sha256'}")
            return 0
        except ValidationError as e:
            print(f"[FAIL] Capture validation failed: {e}", file=sys.stderr)
            return 1
        except Exception as e:
            print(f"[ERROR] Unexpected error during capture: {e}", file=sys.stderr)
            return 1

    elif args.command == "verify":
        evidence_dir = Path(os.path.abspath(args.evidence_dir))
        try:
            print(f"[INFO] Verifying evidence in {evidence_dir}...")
            verify_evidence(
                evidence_dir=evidence_dir,
                repo_root=repo_root,
                verify_source=args.verify_source,
            )
            print(f"[OK] All artifacts in {evidence_dir} verified successfully.")
            print("[OK] Test pass semantics, six-table scale volumes, and plan provenance confirmed.")
            if args.verify_source:
                print("[OK] Source canonical LF digests verified against current repository.")
            return 0
        except ValidationError as e:
            print(f"[FAIL] Evidence verification failed: {e}", file=sys.stderr)
            return 1
        except Exception as e:
            print(f"[ERROR] Unexpected error during verification: {e}", file=sys.stderr)
            return 1

    else:
        parser.print_help()
        return 0


if __name__ == "__main__":
    sys.exit(main())
