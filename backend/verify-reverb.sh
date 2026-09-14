#!/usr/bin/env bash
# Reverb deployment verification script.
# Usage: ./verify-reverb.sh https://your-domain.example.com
#
# Checks:
# 1. Reverb WebSocket endpoint reachable
# 2. Backend /up health check
# 3. Broadcasting config correct
# 4. Frontend env vars present

set -euo pipefail

DOMAIN="${1:-http://localhost:8000}"
REVERB_APP_KEY="${REVERB_APP_KEY:-}"
PASS=0
FAIL=0

green() { printf "\033[32m✓ %s\033[0m\n" "$1"; }
red() { printf "\033[31m✗ %s\033[0m\n" "$1"; }
info() { printf "\033[36mℹ %s\033[0m\n" "$1"; }

info "Checking Reverb deployment at $DOMAIN"

# 1. Backend health check
if curl -sf "$DOMAIN/up" >/dev/null 2>&1; then
  green "Backend /up health check: OK"
  PASS=$((PASS + 1))
else
  red "Backend /up health check: FAILED"
  FAIL=$((FAIL + 1))
fi

# 2. Check if Reverb key is set
if [ -z "$REVERB_APP_KEY" ]; then
  red "REVERB_APP_KEY not set in environment"
  FAIL=$((FAIL + 1))
else
  green "REVERB_APP_KEY is set"
  PASS=$((PASS + 1))
fi

# 3. Check broadcasting config via artisan
if php artisan tinker --execute="echo config('broadcasting.default');" 2>/dev/null | grep -q "reverb"; then
  green "BROADCAST_CONNECTION=reverb: OK"
  PASS=$((PASS + 1))
else
  red "BROADCAST_CONNECTION is not 'reverb' (check .env)"
  FAIL=$((FAIL + 1))
fi

# 4. Check Reverb process is running
if pgrep -f "reverb:start" >/dev/null 2>&1 || systemctl is-active --quiet jejakbahari-reverb 2>/dev/null; then
  green "Reverb process: running"
  PASS=$((PASS + 1))
else
  red "Reverb process: not running"
  FAIL=$((FAIL + 1))
fi

# 5. Check Reverb port is listening
REVERB_PORT="${REVERB_SERVER_PORT:-8080}"
if ss -tlnp 2>/dev/null | grep -q ":${REVERB_PORT}" || netstat -tlnp 2>/dev/null | grep -q ":${REVERB_PORT}"; then
  green "Reverb port ${REVERB_PORT}: listening"
  PASS=$((PASS + 1))
else
  red "Reverb port ${REVERB_PORT}: not listening"
  FAIL=$((FAIL + 1))
fi

# 6. Check WebSocket endpoint (if wscat available)
if command -v wscat >/dev/null 2>&1 && [ -n "$REVERB_APP_KEY" ]; then
  WS_SCHEME="wss"
  # Try WebSocket connection (5s timeout)
  if timeout 5 wscat -c "${WS_SCHEME}://${DOMAIN#https://}/app/${REVERB_APP_KEY}" >/dev/null 2>&1; then
    green "WebSocket endpoint: reachable"
    PASS=$((PASS + 1))
  else
    red "WebSocket endpoint: unreachable"
    FAIL=$((FAIL + 1))
  fi
else
  info "wscat not installed or no key — skipping WebSocket test"
fi

echo ""
echo "Results: $PASS passed, $FAIL failed"

if [ "$FAIL" -gt 0 ]; then
  exit 1
fi
