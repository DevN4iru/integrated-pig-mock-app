#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

echo "===== PIGSTEP SAFE START ====="
echo "Starting containers first so the app command is available..."
docker compose up -d

echo
echo "Running server clock preflight..."
scripts/pigstep-server-preflight.sh

echo
echo "Restarting app after safe clock anchor..."
docker compose restart app

echo
echo "Pigstep safe start complete."
docker compose ps
