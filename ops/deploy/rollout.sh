#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: $0 <source_dir>"
  exit 2
fi

SRC_DIR="$1"
APP_DIR="${APP_DIR:-/var/www/CRMEB}"
RELEASES_DIR="${APP_DIR}/releases"
CURRENT_LINK="${APP_DIR}/current"

TS="$(date +%Y%m%d_%H%M%S)"
NEW_RELEASE="${RELEASES_DIR}/${TS}"

mkdir -p "${RELEASES_DIR}"
mkdir -p "${NEW_RELEASE}"

rsync -a --delete "${SRC_DIR}/" "${NEW_RELEASE}/"
ln -sfn "${NEW_RELEASE}" "${CURRENT_LINK}"

echo "Switched current -> ${NEW_RELEASE}"
