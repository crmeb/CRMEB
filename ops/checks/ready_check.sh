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
DB_PASSWORD="$(get_kv 'PASSWORD')"
REDIS_PASSWORD="$(get_kv 'REDIS_PASSWORD')"

warn_count=0

if [[ "${APP_DEBUG}" != "false" ]]; then
  echo "[WARN] APP_DEBUG is not false (current: ${APP_DEBUG:-<missing>})."
fi

if [[ "${ALLOW_CUSTOM_CODE}" == "true" ]]; then
  echo "[WARN] ALLOW_CUSTOM_CRONTAB_CODE=true (RCE-risk, only enable with strict controls)."
  ((warn_count++)) || true
fi

# 检查数据库密码强度
if [[ -n "${DB_PASSWORD}" ]]; then
  if [[ "${DB_PASSWORD}" == "change_me"* || "${#DB_PASSWORD}" -lt 12 ]]; then
    echo "[WARN] DATABASE.PASSWORD appears weak or is a placeholder. Use a strong password (>=12 chars)."
    ((warn_count++)) || true
  fi
fi

# 检查 Redis 密码
if [[ -z "${REDIS_PASSWORD}" || "${REDIS_PASSWORD}" == "change_me"* ]]; then
  echo "[WARN] REDIS.REDIS_PASSWORD is empty or placeholder. Set a password for production."
  ((warn_count++)) || true
fi

# CORS guidance: if production + no whitelist configured
if [[ "${APP_DEBUG}" == "false" ]]; then
  if [[ -z "${ALLOWED_ORIGINS}" && -z "${COOKIE_DOMAIN}" ]]; then
    echo "[WARN] CORS whitelist is empty (CORS.ALLOWED_ORIGINS and COOKIE.DOMAIN are both empty)."
    echo "       Cross-origin requests will be denied by default. Configure one of them if needed."
  fi
fi

if [[ "${warn_count}" -gt 0 ]]; then
  echo ""
  echo "[INFO] ${warn_count} warning(s) found. Review before production deployment."
fi

echo "[OK] Ready check finished: ${ENV_FILE}"
