#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
READY_CHECK="${ROOT_DIR}/ops/checks/ready_check.sh"

tmp_dir="$(mktemp -d)"
trap 'rm -rf "${tmp_dir}"' EXIT

run_case() {
  local name="$1"
  local env_file="$2"
  local expect_rc="$3"
  shift 3
  local -a expect_lines=("$@")

  local out rc
  out="$(ENV_FILE="${env_file}" "${READY_CHECK}" 2>&1)" || rc=$?
  rc="${rc:-0}"

  if [[ "${rc}" -ne "${expect_rc}" ]]; then
    echo "[FAIL] ${name}: expected rc=${expect_rc}, got rc=${rc}"
    echo "${out}"
    exit 1
  fi

  local line
  for line in "${expect_lines[@]}"; do
    if ! grep -Fq "${line}" <<<"${out}"; then
      echo "[FAIL] ${name}: missing expected output: ${line}"
      echo "${out}"
      exit 1
    fi
  done

  echo "[OK] ${name}"
}

case_good="${tmp_dir}/good.env"
cat >"${case_good}" <<'EOF'
APP_DEBUG=false

[DATABASE]
PASSWORD=ThisIsAStrongPassword_123!

[REDIS]
REDIS_PASSWORD=AnotherStrongPassword_456!

[COOKIE]
DOMAIN=example.com

[SECURITY]
ALLOW_CUSTOM_CRONTAB_CODE=false
EOF

run_case "good env" "${case_good}" 0 "[OK] Ready check finished"

case_placeholder="${tmp_dir}/placeholder.env"
cat >"${case_placeholder}" <<'EOF'
# placeholder only
EOF

run_case "placeholder env" "${case_placeholder}" 2 "[FAIL] Env file looks like a placeholder"

echo "[OK] All tests passed"
