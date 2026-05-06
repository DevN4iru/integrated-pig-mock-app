#!/usr/bin/env bash
set -euo pipefail

WAIT_SECONDS="${PIGSTEP_PREFLIGHT_WAIT_SECONDS:-300}"
MIN_DATE="${PIGSTEP_CLOCK_MIN_DATE:-2026-01-01}"
START_TS="$(date +%s)"

echo "===== PIGSTEP SERVER PREFLIGHT ====="
echo "Host now: $(date -Is)"
echo "Minimum safe date: ${MIN_DATE}"
echo "Wait limit: ${WAIT_SECONDS}s"
echo

date_is_sane() {
  local today
  today="$(date +%F)"
  [[ "${today}" > "${MIN_DATE}" || "${today}" == "${MIN_DATE}" ]]
}

ntp_is_synced_or_unavailable() {
  if command -v timedatectl >/dev/null 2>&1; then
    local sync
    sync="$(timedatectl show -p NTPSynchronized --value 2>/dev/null || true)"

    if [[ "${sync}" == "yes" ]]; then
      return 0
    fi

    if [[ "${PIGSTEP_REQUIRE_HOST_NTP_SYNC:-false}" == "true" ]]; then
      return 1
    fi
  fi

  return 0
}

while true; do
  if date_is_sane && ntp_is_synced_or_unavailable; then
    echo "Host clock sanity: PASS"
    break
  fi

  now_ts="$(date +%s)"
  elapsed="$((now_ts - START_TS))"

  if (( elapsed >= WAIT_SECONDS )); then
    echo "Host clock sanity: FAIL after ${WAIT_SECONDS}s"
    date -Is
    command -v timedatectl >/dev/null 2>&1 && timedatectl || true
    exit 2
  fi

  echo "Waiting for safe clock... ${elapsed}s/${WAIT_SECONDS}s"
  sleep 5
done

echo
echo "===== DOCKER / APP CLOCK CHECK ====="
docker compose ps || true
docker compose exec -T app php artisan pigstep:clock-check --write-anchor

echo
echo "===== PREFLIGHT DONE ====="
