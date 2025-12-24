#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: $0 <backup.sql.gz|backup.sql>"
  exit 2
fi

INPUT="$1"

: "${MYSQL_HOST:?Missing MYSQL_HOST}"
: "${MYSQL_PORT:=3306}"
: "${MYSQL_USER:?Missing MYSQL_USER}"
: "${MYSQL_PASSWORD:?Missing MYSQL_PASSWORD}"
: "${MYSQL_DATABASE:?Missing MYSQL_DATABASE}"

export MYSQL_PWD="${MYSQL_PASSWORD}"

if [[ "${INPUT}" == *.gz ]]; then
  gunzip -c "${INPUT}" | mysql --host="${MYSQL_HOST}" --port="${MYSQL_PORT}" --user="${MYSQL_USER}" "${MYSQL_DATABASE}"
else
  mysql --host="${MYSQL_HOST}" --port="${MYSQL_PORT}" --user="${MYSQL_USER}" "${MYSQL_DATABASE}" < "${INPUT}"
fi

unset MYSQL_PWD
echo "Restore finished: ${INPUT}"
