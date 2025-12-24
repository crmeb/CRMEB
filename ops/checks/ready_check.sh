#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="${ENV_FILE:-${ROOT_DIR}/crmeb/.env}"

if [[ ! -f "${ENV_FILE}" ]]; then
  echo "[FAIL] Missing env file: ${ENV_FILE}"
  echo "       Create it from: ${ROOT_DIR}/crmeb/.env.example"
  exit 2
fi

get_kv() {
  local key="$1"
  # Matches: KEY=value (no spaces). ThinkPHP env is INI-like; this is a best-effort check.
  grep -E "^${key}=" "${ENV_FILE}" | tail -n 1 | cut -d= -f2- || true
}

APP_DEBUG="$(get_kv 'APP_DEBUG')"
ALLOW_CUSTOM_CODE="$(get_kv 'ALLOW_CUSTOM_CRONTAB_CODE')"
COOKIE_DOMAIN="$(get_kv 'DOMAIN')"
ALLOWED_ORIGINS="$(get_kv 'ALLOWED_ORIGINS')"

fail=0

if [[ "${APP_DEBUG}" != "false" ]]; then
  echo "[WARN] APP_DEBUG is not false (current: ${APP_DEBUG:-<missing>})."
fi

if [[ "${ALLOW_CUSTOM_CODE}" == "true" ]]; then
  echo "[WARN] ALLOW_CUSTOM_CRONTAB_CODE=true (RCE-risk, only enable with strict controls)."
fi

# CORS guidance: if production + no whitelist configured
if [[ "${APP_DEBUG}" == "false" ]]; then
  if [[ -z "${ALLOWED_ORIGINS}" && -z "${COOKIE_DOMAIN}" ]]; then
    echo "[WARN] CORS whitelist is empty (CORS.ALLOWED_ORIGINS and COOKIE.DOMAIN are both empty)."
    echo "       Cross-origin requests will be denied by default. Configure one of them if needed."
  fi
fi

if [[ "${fail}" -ne 0 ]]; then
  exit 1
fi

echo "[OK] Ready check finished: ${ENV_FILE}"
