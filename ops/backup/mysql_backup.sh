#!/usr/bin/env bash
set -euo pipefail

: "${MYSQL_HOST:?Missing MYSQL_HOST}"
: "${MYSQL_PORT:=3306}"
: "${MYSQL_USER:?Missing MYSQL_USER}"
: "${MYSQL_PASSWORD:?Missing MYSQL_PASSWORD}"
: "${MYSQL_DATABASE:?Missing MYSQL_DATABASE}"

BACKUP_DIR="${BACKUP_DIR:-./backups}"
TS="$(date +%Y%m%d_%H%M%S)"
OUT="${BACKUP_DIR}/${MYSQL_DATABASE}_${TS}.sql.gz"

mkdir -p "${BACKUP_DIR}"

export MYSQL_PWD="${MYSQL_PASSWORD}"
mysqldump \
  --host="${MYSQL_HOST}" \
  --port="${MYSQL_PORT}" \
  --user="${MYSQL_USER}" \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --default-character-set=utf8mb4 \
  "${MYSQL_DATABASE}" | gzip > "${OUT}"
unset MYSQL_PWD

echo "Backup written: ${OUT}"
