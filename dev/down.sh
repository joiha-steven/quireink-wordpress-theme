#!/usr/bin/env bash
# Stop the viewer. `-v` also throws the database away, which is the point: this stack holds
# no state worth keeping, and a half-migrated database is the one way it could start lying.
set -euo pipefail
cd "$(dirname "$0")"
docker compose down -v
