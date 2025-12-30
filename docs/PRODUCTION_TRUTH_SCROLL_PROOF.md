# Production truth proof (scroll / CSP / head)

This file is a **copy/paste friendly** evidence bundle for what production is serving right now.

## Targets

- Home: https://blackbox.codes/
- Demo: https://blackbox.codes/demo.php

## Commands used (Windows / PowerShell)

### 0) One-shot capture script (recommended)

```powershell
pwsh -File scripts/prod-truth-capture.ps1
```

### 1) Capture full response headers (incl. CSP)

```powershell
$outDir = 'artifacts\\prod-proof'
New-Item -ItemType Directory -Force -Path $outDir | Out-Null

curl.exe -sS -D (Join-Path $outDir 'home.headers.txt') -o NUL https://blackbox.codes/
curl.exe -sS -D (Join-Path $outDir 'demo.headers.txt') -o NUL https://blackbox.codes/demo.php
```

Artifacts:

- [artifacts/prod-proof/home.headers.txt](../artifacts/prod-proof/home.headers.txt)
- [artifacts/prod-proof/demo.headers.txt](../artifacts/prod-proof/demo.headers.txt)

### 2) Capture production HTML to disk (reliable `<head>` extraction)

```powershell
$outDir = 'artifacts\\prod-proof'
New-Item -ItemType Directory -Force -Path $outDir | Out-Null

curl.exe -sS -H "Accept-Encoding: identity" -o (Join-Path $outDir 'home.html') https://blackbox.codes/
curl.exe -sS -H "Accept-Encoding: identity" -o (Join-Path $outDir 'demo.html') https://blackbox.codes/demo.php
```

Artifacts:

- [artifacts/prod-proof/home.html](../artifacts/prod-proof/home.html)
- [artifacts/prod-proof/demo.html](../artifacts/prod-proof/demo.html)

## Raw header proof (CSP)

Open the artifacts above to see the **full unwrapped** CSP line.

High-level confirmation from the captured header files:

- `script-src` includes `'unsafe-inline'` and `'unsafe-eval'`
- `style-src` includes `'unsafe-inline'`

## `<head>` proof (exact snippets)

## Post-deploy acceptance checks (must be true)

After deploy, re-run the capture and confirm these are present in both `/` and `/demo.php` `<head>`:

- `<!-- BBX_HEAD_MARKER: P0 scroll-contract sync + css_version (main) -->`
- `<meta name="css_version" content="...">`
- `scroll-contract.css` loaded as a synchronous stylesheet link:
  - ✅ `<link rel="stylesheet" href="/assets/css/scroll-contract.css?...">`
  - ❌ no `<link rel="preload" ... scroll-contract.css ... onload=...>`

### Home: `scroll-contract.css` include (served in `<head>`)

From the captured production home HTML:

```html
<!-- P0 SCROLL CONTRACT - MUST LOAD LAST (global scroll authority, overrides all other CSS scroll rules) -->
<link
  rel="preload"
  href="/assets/css/scroll-contract.css?v=491013b1"
  as="style"
  onload="this.onload=null;this.rel='stylesheet'"
/>
<noscript
  ><link rel="stylesheet" href="/assets/css/scroll-contract.css?v=491013b1"
/></noscript>
```

### Demo: `scroll-contract.css` include (served in `<head>`)

From the captured production demo HTML:

```html
<!-- P0 SCROLL CONTRACT - MUST LOAD LAST (global scroll authority, overrides all other CSS scroll rules) -->
<link
  rel="preload"
  href="/assets/css/scroll-contract.css?v=491013b1"
  as="style"
  onload="this.onload=null;this.rel='stylesheet'"
/>
<noscript
  ><link rel="stylesheet" href="/assets/css/scroll-contract.css?v=491013b1"
/></noscript>
```

### “critical” marker (inline head script)

Both pages include an inline `<script>` in `<head>` containing this logic:

```js
// P1-E: FOUC Prevention - Add landing-gate class before paint
// This prevents any flash of unstyled content on landing page
// CRITICAL FIX #2: Gate only controls OPACITY, not visibility/scroll
document.documentElement.classList.add("landing-gate");
```

### “failsafe” marker (head style block)

Both pages’ `<head>` includes a CSS comment containing the term `failsafe`:

```css
/* ════ P0 FIX: PERMANENTLY DISABLE SCROLL-BLOCKING OVERLAYS ════
   These fixed-positioned bottom elements capture first touch/wheel events
   on iOS and prevent scroll. Permanent CSS hide as failsafe layer. */
```

### `css_version` marker

Token search in production `<head>` returned **NOT FOUND** for `css_version` on both `/` and `/demo.php`.

## Notes

- This file is intentionally limited to **production truth proof**. It does not claim anything is fixed on iPhone.
