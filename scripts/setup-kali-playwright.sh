#!/usr/bin/env bash
set -euo pipefail

# Minimal, idempotent Playwright setup for Kali (kali-rolling)
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CACHE_DIR="${PLAYWRIGHT_BROWSERS_PATH:-$HOME/.cache/ms-playwright}"

if ! grep -qi "^ID=kali" /etc/os-release && ! grep -qi "^ID_LIKE=.*kali" /etc/os-release; then
  echo "[setup-kali] This script is intended for Kali (kali-rolling). Aborting." >&2
  exit 1
fi

if command -v sudo >/dev/null 2>&1; then
  SUDO="sudo"
else
  SUDO=""
fi

PKGS=(
  fonts-unifont libasound2t64 libasound2-data libatk1.0-0t64 libatk-bridge2.0-0t64
  libcups2t64 libdrm2 libdbus-1-3 libx11-6 libx11-xcb1 libxcb1 libxcb-dri3-0
  libxcomposite1 libxcursor1 libxdamage1 libxfixes3 libxi6 libxrandr2 libxrender1
  libxext6 libgbm1 libgtk-3-0t64 libnss3 libnspr4 libxss1 libxtst6 libxshmfence1
  libxkbcommon0 libxkbcommon-x11-0 libatspi2.0-0t64 libwayland-client0 libwayland-egl1
  libwayland-cursor0 libgdk-pixbuf-2.0-0 libgdk-pixbuf-xlib-2.0-0 libjpeg62-turbo
  libpng16-16t64 libwebp7 libpango-1.0-0 libpangocairo-1.0-0 libpangoft2-1.0-0
  libglib2.0-0t64 libharfbuzz0b libfreetype6
)

cd "$REPO_ROOT"

echo "[setup-kali] Updating apt cache..."
$SUDO apt-get update -y

echo "[setup-kali] Installing Playwright runtime deps for Kali (no recommends)..."
$SUDO apt-get install -y --no-install-recommends "${PKGS[@]}"

echo "[setup-kali] Installing Playwright Chromium browser (no --with-deps)..."
if command -v pnpm >/dev/null 2>&1; then
  pnpm exec playwright install chromium
else
  npx playwright install chromium
fi

echo "[setup-kali] Playwright cache contents: $CACHE_DIR"
ls -1 "$CACHE_DIR" || echo "[setup-kali] Cache directory not found (install may have failed)."

echo "[setup-kali] Done. To run headed GUI tests on Chromium only:"
echo "[setup-kali]   pnpm run qa:gui -- --project=chromium"
