#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/CRMEB}"
RELEASES_DIR="${APP_DIR}/releases"
CURRENT_LINK="${APP_DIR}/current"

mapfile -t releases < <(ls -1 "${RELEASES_DIR}" | sort -r)
if [[ ${#releases[@]} -lt 2 ]]; then
  echo "Not enough releases to rollback."
  exit 1
fi

PREV="${RELEASES_DIR}/${releases[1]}"
ln -sfn "${PREV}" "${CURRENT_LINK}"
echo "Rolled back current -> ${PREV}"
