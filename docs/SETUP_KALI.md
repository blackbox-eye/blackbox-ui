# Playwright on Kali (kali-rolling)

This repo uses Playwright v1.56.1 with projects `chromium`, `firefox`, `webkit`, and an extra `chromium-dark`. The `qa:gui` script runs `tests/e2e/sso-gui.spec.ts` headed; the Playwright config also auto-starts a PHP built-in server on port 8000.

## One-time setup

Run from the repo root.

```bash
# 1) Install JS deps
pnpm install

# 2) Install Kali system libraries (verified with apt-cache policy)
sudo apt-get update
sudo apt-get install -y --no-install-recommends \
  fonts-unifont libasound2t64 libasound2-data libatk1.0-0t64 libatk-bridge2.0-0t64 \
  libcups2t64 libdrm2 libdbus-1-3 libx11-6 libx11-xcb1 libxcb1 libxcb-dri3-0 \
  libxcomposite1 libxcursor1 libxdamage1 libxfixes3 libxi6 libxrandr2 libxrender1 \
  libxext6 libgbm1 libgtk-3-0t64 libnss3 libnspr4 libxss1 libxtst6 libxshmfence1 \
  libxkbcommon0 libxkbcommon-x11-0 libatspi2.0-0t64 libwayland-client0 libwayland-egl1 \
  libwayland-cursor0 libgdk-pixbuf-2.0-0 libgdk-pixbuf-xlib-2.0-0 libjpeg62-turbo \
  libpng16-16t64 libwebp7 libpango-1.0-0 libpangocairo-1.0-0 libpangoft2-1.0-0 \
  libglib2.0-0t64 libharfbuzz0b libfreetype6

# 3) Download Playwright browsers (no --with-deps)
pnpm exec playwright install chromium

# 4) Verify browser cache exists
ls -1 "${PLAYWRIGHT_BROWSERS_PATH:-$HOME/.cache/ms-playwright}" | sed 's/^/ - /'

# 5) Run headed GUI test on Chromium only
pnpm run qa:gui -- --project=chromium
```

## Helper script

Use the idempotent helper if you prefer a single command:

```bash
./scripts/setup-kali-playwright.sh
```

It will:
- Assert it is running on Kali
- apt-get install the verified packages above (without recommends)
- Run `pnpm exec playwright install chromium` (or `npx` if pnpm is missing)
- Print the Playwright cache path for confirmation

## Notes
- `--with-deps` is intentionally **not** used because Playwright’s default Debian/Ubuntu package names do not match Kali’s (ttf-unifont vs. fonts-unifont, libgdk-pixbuf2.0-0 vs. libgdk-pixbuf-xlib-2.0-0, libcups2t64, etc.).
- If you already have some libraries installed, `apt-get install` will simply skip them.
- If you need Firefox/WebKit, rerun step 3 with `firefox webkit` after Chromium succeeds.
