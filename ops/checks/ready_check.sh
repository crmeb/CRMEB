#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="${ENV_FILE:-${ROOT_DIR}/crmeb/.env}"

if [[ ! -f "${ENV_FILE}" ]]; then
  echo "[FAIL] Missing env file: ${ENV_FILE}"
  echo "       Create it from: ${ROOT_DIR}/crmeb/.env.example"
  exit 2
fi

if ! grep -Eq '^[A-Za-z0-9_]+[[:space:]]*=' "${ENV_FILE}"; then
  echo "[FAIL] Env file looks like a placeholder: ${ENV_FILE}"
  echo "       Copy and edit: ${ROOT_DIR}/crmeb/.env.example"
  exit 2
fi

get_ini_kv() {
  local section="$1"
  local key="$2"
  awk -v want_section="${section}" -v want_key="${key}" '
    BEGIN { cur=""; val="" }
    /^[[:space:]]*;/ { next }
    /^[[:space:]]*#/ { next }
    /^[[:space:]]*\\[[^\\]]+\\][[:space:]]*$/ {
      gsub(/^[[:space:]]*\\[|\\][[:space:]]*$/, "", $0)
      cur=$0
      next
    }
    {
      line=$0
      sub(/^[[:space:]]+/, "", line)
      if (line ~ /^[A-Za-z0-9_]+[[:space:]]*=/) {
        split(line, parts, "=")
        k=parts[1]
        gsub(/[[:space:]]+$/, "", k)
        if (k == want_key && cur == want_section) {
          sub(/^[^=]*=/, "", line)
          gsub(/^[[:space:]]+/, "", line)
          gsub(/[[:space:]]+$/, "", line)
          val=line
        }
      }
    }
    END { print val }
  ' "${ENV_FILE}" 2>/dev/null || true
}

get_global_kv() {
  get_ini_kv "" "$1"
}

APP_DEBUG="$(get_global_kv 'APP_DEBUG')"
ALLOW_CUSTOM_CODE="$(get_ini_kv 'SECURITY' 'ALLOW_CUSTOM_CRONTAB_CODE')"
COOKIE_DOMAIN="$(get_ini_kv 'COOKIE' 'DOMAIN')"
ALLOWED_ORIGINS="$(get_ini_kv 'CORS' 'ALLOWED_ORIGINS')"
DB_PASSWORD="$(get_ini_kv 'DATABASE' 'PASSWORD')"
REDIS_PASSWORD="$(get_ini_kv 'REDIS' 'REDIS_PASSWORD')"

warn_count=0

if [[ "${APP_DEBUG}" != "false" ]]; then
  echo "[WARN] APP_DEBUG is not false (current: ${APP_DEBUG:-<missing>})."
  ((warn_count++)) || true
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
    ((warn_count++)) || true
  fi
fi

if [[ "${warn_count}" -gt 0 ]]; then
  echo ""
  echo "[INFO] ${warn_count} warning(s) found. Review before production deployment."
fi

echo "[OK] Ready check finished: ${ENV_FILE}"
