"use strict";

// ==========================================
// LANGUAGE RESOLUTION (client)
// ==========================================
const languageResolver = (() => {
  const STORAGE_KEY = "bbx_lang";
  const COOKIE_NAME = "bbx_lang";
  const ALLOWED = new Set(
    (window.__BBX_ALLOWED_LANGS__ || ["en", "da"]).map((lang) =>
      String(lang).toLowerCase()
    )
  );
  const DEFAULT_LANG = ALLOWED.has("en")
    ? "en"
    : Array.from(ALLOWED)[0] || "en";

  const readCookie = (name) => {
    const match = document.cookie.match(
      new RegExp(
        "(?:^|; )" +
          name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, "\\$1") +
          "=([^;]*)"
      )
    );
    return match ? decodeURIComponent(match[1]) : null;
  };

  const persistLang = (lang) => {
    const normalized = String(lang).toLowerCase();
    if (!ALLOWED.has(normalized)) {
      return;
    }

    try {
      window.localStorage.setItem(STORAGE_KEY, normalized);
    } catch (error) {
      // Ignore storage errors (private mode, etc.)
    }

    document.cookie = `${COOKIE_NAME}=${normalized};path=/;max-age=31536000;samesite=Lax`;
    document.documentElement.setAttribute("lang", normalized);
    document.documentElement.dataset.lang = normalized;
    if (document.body) {
      document.body.dataset.lang = normalized;
    }
    window.BBX_LANG = normalized;
  };

  const cleanLangParam = (hadQueryParam) => {
    if (
      !hadQueryParam ||
      !window.history ||
      typeof window.history.replaceState !== "function"
    ) {
      return;
    }
    const url = new URL(window.location.href);
    url.searchParams.delete("lang");
    window.history.replaceState({}, document.title, url.toString());
  };

  const resolveLang = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const paramLang = urlParams.get("lang");
    if (paramLang && ALLOWED.has(paramLang.toLowerCase())) {
      const normalized = paramLang.toLowerCase();
      persistLang(normalized);
      cleanLangParam(true);
      return normalized;
    }

    let stored = null;
    try {
      stored = window.localStorage.getItem(STORAGE_KEY);
    } catch (error) {
      stored = null;
    }
    if (stored && ALLOWED.has(stored.toLowerCase())) {
      const normalized = stored.toLowerCase();
      persistLang(normalized);
      return normalized;
    }

    const cookieLang = readCookie(COOKIE_NAME);
    if (cookieLang && ALLOWED.has(cookieLang.toLowerCase())) {
      const normalized = cookieLang.toLowerCase();
      persistLang(normalized);
      return normalized;
    }

    const serverLang = (
      window.__BBX_INITIAL_LANG__ ||
      document.documentElement.getAttribute("lang") ||
      ""
    ).toLowerCase();
    if (serverLang && ALLOWED.has(serverLang)) {
      persistLang(serverLang);
      return serverLang;
    }

    persistLang(DEFAULT_LANG);
    return DEFAULT_LANG;
  };

  let activeLang = resolveLang();

  const setLang = (lang, { reload = false } = {}) => {
    const normalized = String(lang).toLowerCase();
    if (!ALLOWED.has(normalized)) {
      return;
    }
    activeLang = normalized;
    persistLang(normalized);
    cleanLangParam(false);
    if (reload) {
      window.location.reload();
    }
  };

  return {
    getLang: () => activeLang,
    setLang,
    persistLang,
    resolveLang,
    allowed: () => Array.from(ALLOWED),
  };
})();

// ==========================================
// JAVASCRIPT I18N SYSTEM
// ==========================================
const i18n = (() => {
  const BASE_LANG = "en";
  let translations = {};
  let activeLang = languageResolver.getLang();
  let loadPromise = null;

  const deepMerge = (base, override) => {
    const output = Array.isArray(base) ? [...base] : { ...base };
    Object.keys(override || {}).forEach((key) => {
      const baseVal = output[key];
      const overrideVal = override[key];
      if (
        overrideVal &&
        typeof overrideVal === "object" &&
        !Array.isArray(overrideVal)
      ) {
        output[key] = deepMerge(
          baseVal && typeof baseVal === "object" ? baseVal : {},
          overrideVal
        );
      } else {
        output[key] = overrideVal;
      }
    });
    return output;
  };

  const fetchJson = async (path) => {
    try {
      const response = await fetch(path, { credentials: "same-origin" });
      if (response.ok) {
        return await response.json();
      }
    } catch (error) {
      console.error("Failed to load translations:", error);
    }
    return {};
  };

  const loadTranslations = async (lang = activeLang) => {
    const targetLang = languageResolver.allowed().includes(lang)
      ? lang
      : BASE_LANG;

    if (loadPromise && targetLang === activeLang) {
      return loadPromise;
    }

    loadPromise = (async () => {
      const base = await fetchJson("/lang/en.json");
      let overlay = {};

      if (targetLang !== BASE_LANG) {
        overlay = await fetchJson(`/lang/${targetLang}.json`);
      }

      translations = deepMerge(base, overlay);
      activeLang = targetLang;
      languageResolver.persistLang(targetLang);
      return translations;
    })();

    return loadPromise;
  };

  void loadTranslations();

  return {
    t: (key, fallback = "") => {
      const keys = String(key).split(".");
      let value = translations;

      for (const part of keys) {
        if (!value || typeof value !== "object" || !(part in value)) {
          return fallback || key;
        }
        value = value[part];
      }

      return typeof value === "string" ? value : fallback || key;
    },
    loadTranslations,
  };
})();

const currencyFormatter = new Intl.NumberFormat(
  languageResolver.getLang() === "da" ? "da-DK" : "en-DK",
  {
    style: "currency",
    currency: "DKK",
    maximumFractionDigits: 0,
  }
);

const THEME_STORAGE_KEY = "bbx-theme";

// ==========================================
// DEBUG UI FLAG (enable with ?debugUI=1)
// ==========================================
const DEBUG_UI = new URLSearchParams(window.location.search).has("debugUI");

// ==========================================
// iOS SCROLL-LOCK FAILSAFE
// Idempotent function to kill scroll-lock on iOS Brave/DuckDuckGo/Safari
// ==========================================
let savedScrollY = 0;

const unlockBodyScroll = (reason = "unknown") => {
  const body = document.body;
  const html = document.documentElement;
  const computedStyle = getComputedStyle(body);

  // P0 FIX: More comprehensive lock detection
  const hasScrollLockClass =
    body.classList.contains("mobile-menu-open") ||
    body.classList.contains("alphabot-locked") ||
    body.classList.contains("modal-open") ||
    body.classList.contains("drawer-open");
  const hasOverflowHidden =
    body.style.overflow === "hidden" ||
    computedStyle.overflow === "hidden" ||
    computedStyle.overflowY === "hidden";
  const hasPositionFixed =
    body.style.position === "fixed" || computedStyle.position === "fixed";
  const hasTouchActionNone = computedStyle.touchAction === "none";

  const isLocked = hasScrollLockClass || hasOverflowHidden || hasPositionFixed;

  if (!isLocked) {
    return; // Idempotent: nothing to do
  }

  // Restore scroll position from saved value or parsed from body.style.top
  let scrollY = savedScrollY;
  if (!scrollY && body.style.top) {
    const parsed = parseInt(body.style.top, 10);
    if (!isNaN(parsed)) {
      scrollY = Math.abs(parsed);
    }
  }

  // Remove all scroll-lock classes (comprehensive)
  body.classList.remove("mobile-menu-open");
  body.classList.remove("alphabot-locked");
  body.classList.remove("modal-open");
  body.classList.remove("drawer-open");
  html.classList.remove("modal-open");
  html.classList.remove("drawer-open");

  // Clear all inline styles that could lock scroll
  body.style.overflow = "";
  body.style.overflowX = "";
  body.style.overflowY = "";
  body.style.position = "";
  body.style.top = "";
  body.style.left = "";
  body.style.right = "";
  body.style.width = "";
  body.style.height = "";
  body.style.touchAction = "";

  // Also clear on html element
  html.style.overflow = "";
  html.style.overflowX = "";
  html.style.overflowY = "";

  // Restore scroll position
  if (scrollY > 0) {
    window.scrollTo(0, scrollY);
  }

  savedScrollY = 0;

  if (DEBUG_UI) {
    console.info("[unlockBodyScroll] Scroll unlocked. Reason:", reason, {
      hadClass: hasScrollLockClass,
      hadOverflow: hasOverflowHidden,
      hadPosition: hasPositionFixed,
      restoredScrollY: scrollY,
    });
  }
};

// ==========================================
// VISUAL DEBUG PANEL (enable with ?debugUI)
// Shows real-time scroll/lock state for development
// ==========================================
function initDebugPanel() {
  if (!DEBUG_UI) return;

  const panel = document.createElement("div");
  panel.id = "bbx-debug-panel";
  panel.innerHTML = `
    <div class="bbx-debug-header">
      <span>🔧 Debug Panel</span>
      <button id="bbx-debug-close">×</button>
    </div>
    <div class="bbx-debug-content">
      <div class="bbx-debug-row"><span>Scroll Y:</span><span id="dbg-scrolly">0</span></div>
      <div class="bbx-debug-row"><span>Body overflow:</span><span id="dbg-overflow">auto</span></div>
      <div class="bbx-debug-row"><span>Body position:</span><span id="dbg-position">static</span></div>
      <div class="bbx-debug-row"><span>Classes:</span><span id="dbg-classes">-</span></div>
      <div class="bbx-debug-row"><span>Touch action:</span><span id="dbg-touch">auto</span></div>
      <div class="bbx-debug-row"><span>Drawer open:</span><span id="dbg-drawer">false</span></div>
      <div class="bbx-debug-row"><span>Alphabot open:</span><span id="dbg-alphabot">false</span></div>
    </div>
    <button id="bbx-debug-unlock" style="width:100%;margin-top:8px;padding:6px;background:#e74c3c;color:#fff;border:none;border-radius:4px;cursor:pointer;">Force Unlock Scroll</button>
  `;

  // Inject styles
  const style = document.createElement("style");
  style.textContent = `
    #bbx-debug-panel {
      position: fixed;
      bottom: 10px;
      left: 10px;
      width: 260px;
      background: rgba(0,0,0,0.9);
      color: #0f0;
      font-family: monospace;
      font-size: 11px;
      z-index: 2147483647;
      border-radius: 8px;
      border: 1px solid rgba(0,255,0,0.3);
      box-shadow: 0 4px 20px rgba(0,0,0,0.5);
      overflow: hidden;
    }
    #bbx-debug-panel.collapsed { display: none; }
    .bbx-debug-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 10px;
      background: rgba(0,255,0,0.1);
      border-bottom: 1px solid rgba(0,255,0,0.2);
    }
    .bbx-debug-header button {
      background: none;
      border: none;
      color: #0f0;
      font-size: 16px;
      cursor: pointer;
    }
    .bbx-debug-content { padding: 8px 10px; }
    .bbx-debug-row {
      display: flex;
      justify-content: space-between;
      padding: 3px 0;
      border-bottom: 1px solid rgba(0,255,0,0.1);
    }
    .bbx-debug-row span:last-child { color: #0ff; max-width: 150px; overflow: hidden; text-overflow: ellipsis; }
  `;

  document.head.appendChild(style);
  document.body.appendChild(panel);

  // Close button
  document.getElementById("bbx-debug-close").addEventListener("click", () => {
    panel.classList.toggle("collapsed");
  });

  // Force unlock button
  document.getElementById("bbx-debug-unlock").addEventListener("click", () => {
    unlockBodyScroll("manual-debug-unlock");
  });

  // Update loop
  function updateDebugInfo() {
    const body = document.body;
    const computed = getComputedStyle(body);
    const lockClasses = [
      "mobile-menu-open",
      "alphabot-locked",
      "modal-open",
      "drawer-open",
    ];
    const activeClasses = lockClasses.filter((c) => body.classList.contains(c));

    document.getElementById("dbg-scrolly").textContent = Math.round(
      window.scrollY
    );
    document.getElementById("dbg-overflow").textContent =
      computed.overflow + " / " + computed.overflowY;
    document.getElementById("dbg-position").textContent = computed.position;
    document.getElementById("dbg-classes").textContent = activeClasses.length
      ? activeClasses.join(", ")
      : "-";
    document.getElementById("dbg-touch").textContent = computed.touchAction;
    document.getElementById("dbg-drawer").textContent = body.classList.contains(
      "mobile-menu-open"
    )
      ? "✓"
      : "-";
    document.getElementById("dbg-alphabot").textContent =
      document.querySelector(".alphabot-widget.open") ? "✓" : "-";

    requestAnimationFrame(updateDebugInfo);
  }

  updateDebugInfo();
  console.info("[DEBUG_UI] Debug panel initialized. Use ?debugUI to show.");
}

document.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("js-enabled");

  // Initialize debug panel if enabled
  initDebugPanel();

  // ==========================================
  // CRITICAL FIX #2: IMMEDIATE SCROLL UNLOCK ON LANDING PAGE
  // Ensure scroll works from first pixel, no delays
  // ==========================================
  const isLandingPage =
    document.body.classList.contains("page-home") ||
    document.body.classList.contains("page-index");

  if (isLandingPage) {
    // Force unlock scroll immediately on landing page
    unlockBodyScroll("landing-page-init");
    // Also ensure html/body don't have overflow hidden
    document.documentElement.style.overflow = "";
    document.body.style.overflow = "";

    if (DEBUG_UI) {
      console.info(
        "[Landing Page] Scroll unlocked immediately on DOMContentLoaded"
      );
    }
  }

  // ==========================================
  // DEFENSIVE SCROLL-LOCK EVENT LISTENERS
  // Protects against iOS Brave/DuckDuckGo scroll-lock bugs
  // ==========================================
  window.addEventListener("pageshow", (event) => {
    // bfcache restoration can leave body locked
    if (event.persisted) {
      unlockBodyScroll("pageshow-bfcache");
    } else {
      unlockBodyScroll("pageshow");
    }
  });

  document.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible") {
      unlockBodyScroll("visibilitychange");
    }
  });

  window.addEventListener("orientationchange", () => {
    setTimeout(() => unlockBodyScroll("orientationchange"), 100);
  });

  window.addEventListener(
    "resize",
    (() => {
      let resizeTimer = null;
      return () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => unlockBodyScroll("resize"), 150);
      };
    })()
  );

  window.addEventListener("hashchange", () => {
    unlockBodyScroll("hashchange");
  });

  // P0 FIX: Enhanced touchstart failsafe for iOS Safari/Brave/DuckDuckGo
  // If body appears locked but no legitimate overlay is visible, force unlock
  document.addEventListener(
    "touchstart",
    () => {
      const body = document.body;
      const computedStyle = getComputedStyle(body);

      // More comprehensive lock detection
      const hasScrollLockClass =
        body.classList.contains("mobile-menu-open") ||
        body.classList.contains("alphabot-locked") ||
        body.classList.contains("modal-open");
      const hasOverflowHidden =
        body.style.overflow === "hidden" ||
        computedStyle.overflow === "hidden" ||
        computedStyle.overflowY === "hidden";
      const hasPositionFixed = computedStyle.position === "fixed";

      const isLocked =
        hasScrollLockClass || hasOverflowHidden || hasPositionFixed;

      if (!isLocked) {
        return;
      }

      // Check if any overlay is actually visible
      const mobileMenuOverlay = document.getElementById("mobile-menu-overlay");
      const drawerOverlay = document.querySelector(".bbx-drawer-overlay");
      const alphabotPanel = document.getElementById("alphabot-panel");
      const modalOpen = document.querySelector(
        '.modal.is-open, [role="dialog"][aria-hidden="false"]'
      );
      const anyExpanded = document.querySelector('[aria-expanded="true"]');

      const overlayVisible =
        (mobileMenuOverlay && mobileMenuOverlay.classList.contains("active")) ||
        (drawerOverlay && getComputedStyle(drawerOverlay).display !== "none") ||
        (alphabotPanel &&
          alphabotPanel.getAttribute("aria-hidden") === "false") ||
        modalOpen ||
        anyExpanded;

      if (!overlayVisible) {
        if (DEBUG_UI) {
          console.warn(
            "[Touchstart Failsafe] Body locked but no overlay visible - forcing unlock",
            {
              hasScrollLockClass,
              hasOverflowHidden,
              hasPositionFixed,
            }
          );
        }
        unlockBodyScroll("touchstart-failsafe");
      }
    },
    { passive: true }
  );

  // P0 FIX: Additional iOS Safari failsafe - run on every scroll attempt
  // If user tries to scroll and can't, force unlock after brief delay
  let scrollFailsafeTimeout = null;
  document.addEventListener(
    "touchmove",
    (e) => {
      // Only run on landing page where scroll-lock has been a problem
      if (
        !document.body.classList.contains("page-home") &&
        !document.body.classList.contains("page-index")
      ) {
        return;
      }

      // Don't interfere if a legitimate overlay is open
      const mobileMenuOverlay = document.getElementById("mobile-menu-overlay");
      if (mobileMenuOverlay && mobileMenuOverlay.classList.contains("active")) {
        return;
      }

      // Check if scroll is actually blocked (can't scroll despite touchmove)
      clearTimeout(scrollFailsafeTimeout);
      scrollFailsafeTimeout = setTimeout(() => {
        const body = document.body;
        const computedStyle = getComputedStyle(body);
        if (
          computedStyle.position === "fixed" ||
          computedStyle.overflow === "hidden"
        ) {
          if (DEBUG_UI) {
            console.warn(
              "[Touchmove Failsafe] Scroll blocked during touchmove - forcing unlock"
            );
          }
          unlockBodyScroll("touchmove-failsafe");
        }
      }, 100);
    },
    { passive: true }
  );

  const releaseLandingBody = () => {
    if (!document.body.classList.contains("landing-gate")) {
      return;
    }
    document.body.classList.add("landing-ready");
    document.body.classList.remove("landing-gate");
  };

  // P0-5: Enable transitions after first paint to prevent FOUC
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      document.body.classList.add("fouc-ready");

      // Landing isolation: release gate only after head gate signals ready
      if (document.body.classList.contains("landing-gate")) {
        if (document.documentElement.classList.contains("landing-ready")) {
          releaseLandingBody();
        } else {
          window.addEventListener("bbx:landing-ready", releaseLandingBody, {
            once: true,
          });
        }
      }
    });
  });

  const docEl = document.documentElement;
  const bodyEl = document.body;
  const metaColorScheme = document.querySelector('meta[name="color-scheme"]');
  const themeToggleButtons = document.querySelectorAll("[data-theme-toggle]");
  const prefersLight = window.matchMedia("(prefers-color-scheme: light)");
  let storedTheme = null;

  try {
    const candidate = window.localStorage.getItem(THEME_STORAGE_KEY);
    if (candidate === "light" || candidate === "dark") {
      storedTheme = candidate;
    }
  } catch (error) {
    storedTheme = null;
  }

  const setColorSchemeMeta = (theme) => {
    if (!metaColorScheme) {
      return;
    }
    metaColorScheme.setAttribute(
      "content",
      theme === "light" ? "light dark" : "dark light"
    );
  };

  const syncThemeControls = (theme) => {
    const isLight = theme === "light";
    themeToggleButtons.forEach((button) => {
      button.setAttribute("aria-pressed", isLight ? "true" : "false");

      const label = isLight
        ? button.dataset.themeLabelDark || button.getAttribute("aria-label")
        : button.dataset.themeLabelLight || button.getAttribute("aria-label");
      if (label) {
        button.setAttribute("aria-label", label);
      }

      const textEl = button.querySelector(".theme-toggle__text");
      if (textEl) {
        textEl.textContent = isLight
          ? button.dataset.themeTextDark || textEl.textContent
          : button.dataset.themeTextLight || textEl.textContent;
      }
    });
  };

  const applyTheme = (theme, { persist = false } = {}) => {
    const nextTheme = theme === "light" ? "light" : "dark";
    if (bodyEl) {
      bodyEl.dataset.theme = nextTheme;
    }
    docEl.dataset.theme = nextTheme;
    docEl.style.colorScheme = nextTheme;
    setColorSchemeMeta(nextTheme);
    syncThemeControls(nextTheme);

    if (persist) {
      try {
        window.localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
        storedTheme = nextTheme;
      } catch (error) {
        // Ignore storage failures (private mode, etc.)
      }
    }
  };

  const initialTheme =
    storedTheme ||
    (bodyEl && bodyEl.dataset.theme === "light" ? "light" : null) ||
    (docEl.dataset.theme === "light" ? "light" : "dark");
  applyTheme(initialTheme);

  themeToggleButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const currentTheme =
        (bodyEl && bodyEl.dataset.theme === "light") ||
        docEl.dataset.theme === "light"
          ? "light"
          : "dark";
      const nextTheme = currentTheme === "light" ? "dark" : "light";
      applyTheme(nextTheme, { persist: true });
    });
  });

  // Language toggles (desktop + mobile)
  const languageSwitches = document.querySelectorAll("[data-lang-target]");
  const handleLanguageToggle = (event) => {
    event.preventDefault();
    const targetLang = event.currentTarget.getAttribute("data-lang-target");
    if (!targetLang) {
      return;
    }
    languageResolver.setLang(targetLang, { reload: true });
  };
  languageSwitches.forEach((switchEl) => {
    switchEl.addEventListener("click", handleLanguageToggle);
  });

  const handleSystemThemeChange = (event) => {
    if (storedTheme) {
      return;
    }
    applyTheme(event.matches ? "light" : "dark");
  };

  if (typeof prefersLight.addEventListener === "function") {
    prefersLight.addEventListener("change", handleSystemThemeChange);
  } else if (typeof prefersLight.addListener === "function") {
    prefersLight.addListener(handleSystemThemeChange);
  }

  const header = document.getElementById("main-header");
  const updateHeaderScrollState = () => {
    if (!header) {
      return;
    }
    if (window.scrollY > 50) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  };

  let headerScrollThrottle;
  updateHeaderScrollState();
  window.addEventListener(
    "scroll",
    () => {
      if (!headerScrollThrottle) {
        headerScrollThrottle = window.setTimeout(() => {
          updateHeaderScrollState();
          headerScrollThrottle = null;
        }, 10);
      }
    },
    { passive: true }
  );

  const mobileMenuButton = document.getElementById("mobile-menu-button");
  const mobileMenuClose = document.getElementById("mobile-menu-close");
  const mobileMenu = document.getElementById("mobile-menu");
  const mobileMenuOverlay = document.getElementById("mobile-menu-overlay");
  const mobileNavLinks = document.querySelectorAll(".nav-link-mobile");

  if (mobileMenuButton && mobileMenu && mobileMenuOverlay) {
    let lastFocusedElement = null;
    let focusTrapListener = null;
    const focusableSelectors =
      'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])';

    const getFocusableElements = () =>
      Array.from(mobileMenu.querySelectorAll(focusableSelectors)).filter(
        (element) =>
          !element.hasAttribute("disabled") &&
          element.getAttribute("tabindex") !== "-1"
      );

    const enableFocusTrap = () => {
      disableFocusTrap();
      focusTrapListener = (event) => {
        if (event.key !== "Tab") {
          return;
        }

        const focusable = getFocusableElements();
        if (!focusable.length) {
          return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      };

      mobileMenu.addEventListener("keydown", focusTrapListener);
    };

    const disableFocusTrap = () => {
      if (focusTrapListener) {
        mobileMenu.removeEventListener("keydown", focusTrapListener);
        focusTrapListener = null;
      }
    };

    const openMobileMenu = () => {
      // Save scroll position for restoration on close
      savedScrollY = window.scrollY || window.pageYOffset || 0;

      lastFocusedElement =
        document.activeElement instanceof HTMLElement
          ? document.activeElement
          : null;
      mobileMenu.classList.add("active");
      mobileMenu.setAttribute("aria-hidden", "false");
      mobileMenuButton.setAttribute("aria-expanded", "true");
      mobileMenuOverlay.classList.add("active");
      mobileMenuOverlay.setAttribute("aria-hidden", "false");
      // P0 FIX: NO scroll-lock - user must be able to scroll freely
      // document.body.style.overflow = "hidden"; // REMOVED - violates P0 scroll policy
      document.body.classList.add("mobile-menu-open");

      enableFocusTrap();

      window.setTimeout(() => {
        const firstFocusable = getFocusableElements()[0];
        firstFocusable?.focus();
      }, 100);
    };

    const closeMobileMenu = () => {
      disableFocusTrap();
      mobileMenu.classList.remove("active");
      mobileMenu.setAttribute("aria-hidden", "true");
      mobileMenuButton.setAttribute("aria-expanded", "false");
      mobileMenuOverlay.classList.remove("active");
      mobileMenuOverlay.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";
      document.body.classList.remove("mobile-menu-open");

      if (lastFocusedElement) {
        lastFocusedElement.focus();
        lastFocusedElement = null;
      }

      // iOS scroll-lock failsafe: ensure body is fully unlocked after close
      setTimeout(() => {
        requestAnimationFrame(() => {
          unlockBodyScroll("after-close");
        });
      }, 0);
    };

    mobileMenuButton.addEventListener("click", () => {
      if (mobileMenu.classList.contains("active")) {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    });

    mobileMenuClose?.addEventListener("click", closeMobileMenu);
    mobileMenuOverlay.addEventListener("click", closeMobileMenu);

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && mobileMenu.classList.contains("active")) {
        event.preventDefault();
        closeMobileMenu();
      }
    });

    mobileNavLinks.forEach((link) => {
      link.addEventListener("click", closeMobileMenu);
    });
  }

  if (header) {
    const toggleHeaderGlass = () => {
      header.classList.toggle("header-glass", window.scrollY > 50);
    };
    toggleHeaderGlass();
    window.addEventListener("scroll", toggleHeaderGlass, { passive: true });
  }

  const moreDropdownWrapper = document.querySelector(".more-dropdown-wrapper");
  if (moreDropdownWrapper) {
    const moreTrigger = moreDropdownWrapper.querySelector(
      ".more-dropdown-trigger"
    );
    const moreMenu = moreDropdownWrapper.querySelector(".more-dropdown-menu");
    let isMoreOpen = false;

    const positionMoreMenu = () => {
      if (!moreTrigger || !moreMenu) {
        return;
      }
      const rect = moreTrigger.getBoundingClientRect();
      const menuWidth = moreMenu.offsetWidth || 240;
      const maxLeft = Math.max(12, window.innerWidth - menuWidth - 12);
      const left = Math.min(Math.max(rect.right - menuWidth, 12), maxLeft);
      const top = Math.max(rect.bottom + 10, 10);
      moreMenu.style.setProperty("--more-menu-left", `${left}px`);
      moreMenu.style.setProperty("--more-menu-top", `${top}px`);
    };

    const openMoreMenu = () => {
      positionMoreMenu();
      moreMenu?.classList.add("is-open");
      moreTrigger?.setAttribute("aria-expanded", "true");
      isMoreOpen = true;
    };

    const closeMoreMenu = () => {
      moreMenu?.classList.remove("is-open");
      moreTrigger?.setAttribute("aria-expanded", "false");
      isMoreOpen = false;
    };

    moreTrigger?.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      if (isMoreOpen) {
        closeMoreMenu();
      } else {
        openMoreMenu();
      }
    });

    document.addEventListener("click", (event) => {
      if (!moreMenu || !moreTrigger) {
        return;
      }
      if (
        !moreMenu.contains(event.target) &&
        !moreTrigger.contains(event.target)
      ) {
        closeMoreMenu();
      }
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        closeMoreMenu();
      }
    });

    window.addEventListener("resize", () => {
      if (isMoreOpen) {
        positionMoreMenu();
      }
    });
  }

  // Bottom CTA offset coordination (shared for sticky-cta + graphene-cta-bar)
  // Ensures assistant toggle sits above any fixed CTA bar on mobile
  const setBottomCtaOffset = () => {
    const gap = 16; // Gap between CTA bar and assistant
    let offset = 0;

    // Helper: measure element if visible and fixed
    const measureIfVisibleAndFixed = (element) => {
      if (!element) {
        return 0;
      }

      const style = window.getComputedStyle(element);

      // Only measure if position is fixed (mobile layout)
      if (style.position !== "fixed") {
        return 0;
      }

      // Skip if display:none or visibility:hidden
      if (style.display === "none" || style.visibility === "hidden") {
        return 0;
      }

      // Skip if opacity is 0 (transitioning out)
      if (parseFloat(style.opacity) < 0.1) {
        return 0;
      }

      // Skip if data-hidden attribute is present
      if (element.hasAttribute("data-hidden")) {
        return 0;
      }

      const rect = element.getBoundingClientRect();
      return Math.ceil(rect.height);
    };

    // Measure sticky-cta (footer CTA bar)
    const sticky = document.querySelector('[data-component="sticky-cta"]');
    const stickyHeight = measureIfVisibleAndFixed(sticky);
    if (stickyHeight > 0) {
      offset = Math.max(offset, stickyHeight + gap);
    }

    // Measure graphene-cta-bar (home page CTA bar)
    const graphene = document.querySelector(".graphene-cta-bar");
    const grapheneHeight = measureIfVisibleAndFixed(graphene);
    if (grapheneHeight > 0) {
      offset = Math.max(offset, grapheneHeight + gap);
    }

    document.documentElement.style.setProperty(
      "--bbx-sticky-cta-height",
      offset ? `${offset}px` : "0px"
    );
  };

  const scheduleBottomCtaOffset = () => {
    window.requestAnimationFrame(() => {
      window.requestAnimationFrame(setBottomCtaOffset);
    });
  };

  window.addEventListener("resize", scheduleBottomCtaOffset, { passive: true });

  // === STICKY CTA BAR ===
  // Best practice: scroll-triggered visibility, dismiss on tap, sessionStorage persistence
  // Uses hidden attribute + data-hidden for CSS visibility control
  const stickyCtaBar = document.querySelector('[data-component="sticky-cta"]');
  if (stickyCtaBar) {
    const STORAGE_KEY = "bbxStickyCtaDismissed";
    const SCROLL_THRESHOLD = 0.3; // Show after 30% viewport scroll for parity
    const closeButton = stickyCtaBar.querySelector("[data-sticky-cta-close]");
    const ctaButtons = stickyCtaBar.querySelectorAll(
      ".sticky-cta-bar__btn, .sticky-cta-bar__cta"
    );
    let hasBeenShown = false;
    let isCurrentlyDismissed = false; // Local state for this session

    // Check if already dismissed this session
    const checkDismissed = () => {
      try {
        return window.sessionStorage.getItem(STORAGE_KEY) === "1";
      } catch (e) {
        return false;
      }
    };

    // Force hide - uses multiple methods for maximum browser compatibility
    const forceHide = () => {
      stickyCtaBar.hidden = true;
      stickyCtaBar.setAttribute("hidden", "");
      stickyCtaBar.setAttribute("data-hidden", "true");
      stickyCtaBar.setAttribute("aria-hidden", "true");
      stickyCtaBar.removeAttribute("data-visible");
      stickyCtaBar.classList.remove("is-visible");
      stickyCtaBar.classList.add("is-dismissed");
      isCurrentlyDismissed = true;
      scheduleBottomCtaOffset();
    };

    // Show = visible state (only if not dismissed)
    const showBar = () => {
      // CRITICAL: Never show if dismissed
      if (isCurrentlyDismissed || checkDismissed()) {
        forceHide();
        return;
      }
      stickyCtaBar.hidden = false;
      stickyCtaBar.removeAttribute("hidden");
      stickyCtaBar.removeAttribute("data-hidden");
      stickyCtaBar.removeAttribute("aria-hidden");
      stickyCtaBar.setAttribute("data-visible", "true");
      stickyCtaBar.classList.add("is-visible");
      stickyCtaBar.classList.remove("is-dismissed");
      hasBeenShown = true;
      scheduleBottomCtaOffset();
    };

    // Persist dismissal in sessionStorage
    const persistDismissal = () => {
      try {
        window.sessionStorage.setItem(STORAGE_KEY, "1");
      } catch (e) {
        // Ignore storage failures
      }
    };

    // Scroll handler: show after threshold (only if not dismissed)
    const handleScroll = () => {
      // Double-check dismissal state on every scroll
      if (hasBeenShown || isCurrentlyDismissed || checkDismissed()) return;
      const scrollY = window.scrollY || window.pageYOffset;
      if (scrollY > window.innerHeight * SCROLL_THRESHOLD) {
        window.requestAnimationFrame(showBar);
      }
    };

    // Initialize: check dismissal FIRST
    isCurrentlyDismissed = checkDismissed();
    if (isCurrentlyDismissed) {
      // CRITICAL: Force hide immediately and don't register scroll listener
      forceHide();
    } else {
      // Start hidden, wait for scroll to show
      forceHide();
      isCurrentlyDismissed = false; // Reset since we just hid for initial state
      window.addEventListener("scroll", handleScroll, { passive: true });
      // Check initial scroll position
      handleScroll();
    }
    scheduleBottomCtaOffset();

    // Dismiss handler with pointerdown for better mobile support
    const handleDismiss = (e) => {
      e.preventDefault();
      e.stopPropagation();
      forceHide();
      persistDismissal();
    };

    // Event listeners on close button
    if (closeButton) {
      // pointerdown for immediate response (before click)
      closeButton.addEventListener("pointerdown", handleDismiss, {
        passive: false,
      });
      // Fallback click for older browsers
      closeButton.addEventListener("click", handleDismiss, { passive: false });
    }

    // Track CTA clicks for analytics (once per session)
    ctaButtons.forEach((btn) => {
      btn.addEventListener("click", persistDismissal, { once: true });
    });

    // Footer docking behavior
    const footer = document.querySelector("footer, .site-footer");
    if (footer) {
      const footerObserver = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (isCurrentlyDismissed || checkDismissed()) return;
            if (entry.isIntersecting) {
              stickyCtaBar.classList.add("is-docked");
            } else {
              stickyCtaBar.classList.remove("is-docked");
            }
          });
        },
        { threshold: 0.05 }
      );
      footerObserver.observe(footer);
    }
  }

  // Graphene CTA bar: mobile-first, visible by default (hide only when dismissed)
  const grapheneCtaBar = document.querySelector(".graphene-cta-bar");
  if (grapheneCtaBar) {
    const GRAPHENE_STORAGE_KEY = "bbxGrapheneCtaDismissed";
    const mobileBreakpoint = window.matchMedia("(max-width: 900px)");

    const isGrapheneDismissed = () => {
      try {
        return window.sessionStorage.getItem(GRAPHENE_STORAGE_KEY) === "1";
      } catch (error) {
        return false;
      }
    };

    const hideGrapheneBar = () => {
      grapheneCtaBar.setAttribute("data-hidden", "true");
      grapheneCtaBar.removeAttribute("data-visible");
      scheduleBottomCtaOffset();
    };

    const showGrapheneBar = () => {
      grapheneCtaBar.removeAttribute("data-hidden");
      grapheneCtaBar.setAttribute("data-visible", "true");
      scheduleBottomCtaOffset();
    };

    const persistGrapheneDismissal = () => {
      try {
        window.sessionStorage.setItem(GRAPHENE_STORAGE_KEY, "1");
      } catch (error) {
        // Ignore storage failures
      }
    };

    const initializeGrapheneCta = () => {
      if (isGrapheneDismissed()) {
        hideGrapheneBar();
        return;
      }
      showGrapheneBar();
    };

    initializeGrapheneCta();
    scheduleBottomCtaOffset();

    mobileBreakpoint.addEventListener("change", (event) => {
      if (isGrapheneDismissed()) {
        return;
      }
      if (event.matches) {
        showGrapheneBar();
      } else {
        grapheneCtaBar.removeAttribute("data-hidden");
        grapheneCtaBar.setAttribute("data-visible", "true");
        scheduleBottomCtaOffset();
      }
    });

    const grapheneCloseBtn = grapheneCtaBar.querySelector(
      "[data-graphene-cta-close]"
    );
    grapheneCloseBtn?.addEventListener("click", () => {
      hideGrapheneBar();
      persistGrapheneDismissal();
    });

    const grapheneActionButtons = grapheneCtaBar.querySelectorAll("a");
    grapheneActionButtons.forEach((button) => {
      button.addEventListener("click", persistGrapheneDismissal, {
        once: true,
      });
    });

    // Hide CTA bar when footer is visible (prevents overlap)
    const footer = document.querySelector("footer, .site-footer");
    if (footer) {
      const footerObserver = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (isGrapheneDismissed()) {
              return;
            }
            if (entry.isIntersecting) {
              // Footer is visible - dock the CTA bar
              grapheneCtaBar.classList.add("is-docked");
              grapheneCtaBar.setAttribute("data-footer-visible", "true");
            } else {
              // Footer not visible - show the CTA bar
              grapheneCtaBar.classList.remove("is-docked");
              grapheneCtaBar.removeAttribute("data-footer-visible");
            }
            scheduleBottomCtaOffset();
          });
        },
        { threshold: 0.1 }
      ); // Trigger when 10% of footer is visible

      footerObserver.observe(footer);
    }
  }

  // Ensure initial bottom CTA offset is correct even when sticky-cta is hidden (e.g., home page).
  scheduleBottomCtaOffset();

  const fadeSections = document.querySelectorAll(".section-fade-in");
  if (fadeSections.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
          }
        });
      },
      { threshold: 0.1 }
    );

    fadeSections.forEach((section) => observer.observe(section));
  }

  const recaptchaSiteKey = (window.RECAPTCHA_SITE_KEY || "").trim();
  const recaptchaDebug = Boolean(window.RECAPTCHA_DEBUG);
  const recaptchaLog = (...args) => {
    if (recaptchaDebug) {
      console.log("[reCAPTCHA]", ...args);
    }
  };
  const recaptchaError = (...args) => {
    if (recaptchaDebug) {
      console.error("[reCAPTCHA]", ...args);
    }
  };

  const recaptchaHasClients = () => {
    if (
      typeof grecaptcha === "undefined" ||
      typeof grecaptcha.reset !== "function"
    ) {
      return false;
    }
    const cfg = grecaptcha.___grecaptcha_cfg;
    if (!cfg || !cfg.clients) {
      return false;
    }
    return Object.keys(cfg.clients).length > 0;
  };

  const fetchRecaptchaToken = async (action = "contact") => {
    if (!recaptchaSiteKey) {
      recaptchaLog("Site key missing, skipping token fetch");
      return "";
    }
    if (typeof grecaptcha === "undefined") {
      recaptchaError(
        "RECAPTCHA FRONTEND ERROR: grecaptcha not loaded - script may be blocked or site key invalid"
      );
      return "";
    }

    return new Promise((resolve) => {
      try {
        const readyTimeout = window.setTimeout(() => {
          recaptchaError(
            "RECAPTCHA FRONTEND ERROR: ready() timeout - site key may be invalid"
          );
          resolve("");
        }, 5000);

        grecaptcha.ready(() => {
          window.clearTimeout(readyTimeout);
          recaptchaLog(`Executing reCAPTCHA with action "${action}"`);
          grecaptcha
            .execute(recaptchaSiteKey, { action })
            .then((token) => {
              if (token) {
                recaptchaLog("Token generated (length:", token.length, ")");
              } else {
                recaptchaError(
                  "RECAPTCHA FRONTEND ERROR: Empty token returned"
                );
              }
              resolve(token || "");
            })
            .catch((error) => {
              recaptchaError(
                "RECAPTCHA FRONTEND ERROR: Execute failed -",
                error?.message || error
              );
              resolve("");
            });
        });
      } catch (error) {
        recaptchaError(
          "RECAPTCHA FRONTEND ERROR: Initialization failed -",
          error?.message || error
        );
        resolve("");
      }
    });
  };

  const parseJsonResponse = async (response) => {
    const contentType = response.headers.get("content-type") || "";
    if (contentType.includes("application/json")) {
      return response.json();
    }
    const text = await response.text();
    return { status: response.ok ? "ok" : "error", message: text };
  };

  const setSubmittingState = (button, isSubmitting, loadingText = "...") => {
    if (!button) {
      return;
    }

    if (isSubmitting) {
      button.disabled = true;
      if (!button.dataset.originalText) {
        button.dataset.originalText = button.textContent || "";
      }
      button.textContent = loadingText;
    } else {
      button.disabled = false;
      if (button.dataset.originalText) {
        button.textContent = button.dataset.originalText;
      }
    }
  };

  const showFieldError = (input, message) => {
    if (!input) {
      return;
    }
    input.classList.add("field-error");
    input.setAttribute("aria-invalid", "true");

    // Find error element - check for data-error-for first, then aria-describedby
    let errorEl = document.querySelector(`[data-error-for="${input.id}"]`);
    if (!errorEl) {
      const messageIds = input.getAttribute("aria-describedby");
      if (messageIds) {
        const ids = messageIds.split(" ");
        const errorId = ids.find((id) => id.includes("error"));
        if (errorId) {
          errorEl = document.getElementById(errorId);
        }
      }
    }

    if (errorEl) {
      errorEl.textContent = message;
      errorEl.classList.remove("hidden");
    }
  };

  const clearFieldError = (input) => {
    if (!input) {
      return;
    }
    input.classList.remove("field-error");
    input.removeAttribute("aria-invalid");

    // Find error element - check for data-error-for first, then aria-describedby
    let errorEl = document.querySelector(`[data-error-for="${input.id}"]`);
    if (!errorEl) {
      const messageIds = input.getAttribute("aria-describedby");
      if (messageIds) {
        const ids = messageIds.split(" ");
        const errorId = ids.find((id) => id.includes("error"));
        if (errorId) {
          errorEl = document.getElementById(errorId);
        }
      }
    }

    if (errorEl) {
      errorEl.textContent = "";
      errorEl.classList.add("hidden");
    }
  };

  const showSkeletonLoader = (container) => {
    if (!container) {
      return;
    }
    container.innerHTML = `
            <div class="space-y-4">
                <div class="h-4 rounded bg-gray-800/60 animate-pulse"></div>
                <div class="h-4 rounded bg-gray-800/40 animate-pulse"></div>
                <div class="h-4 rounded bg-gray-800/60 animate-pulse"></div>
            </div>
        `;
    container.classList.remove("hidden");
  };

  const showAILoadingState = (element, message) => {
    if (!element) {
      return;
    }
    element.innerHTML = `
            <div class="flex items-center gap-3 text-amber-300">
                <span class="inline-flex h-3 w-3 rounded-full bg-amber-400 animate-pulse"></span>
                <span>${message}</span>
            </div>
        `;
    element.classList.remove("hidden");
  };

  const contactForm = document.getElementById("contact-form");
  if (contactForm) {
    const formSuccessMessage = document.getElementById("contact-form-success");
    const formErrorMessage = document.getElementById("contact-form-error");
    const submitButton = contactForm.querySelector('button[type="submit"]');
    const formEndpoint =
      contactForm.dataset.endpoint ||
      contactForm.getAttribute("action") ||
      "contact-submit.php";

    recaptchaLog("[Contact Form] Configuration:", {
      endpoint: formEndpoint,
      recaptchaSiteKey: recaptchaSiteKey
        ? `${recaptchaSiteKey.substring(0, 20)}...`
        : "[EMPTY]",
      grecaptchaLoaded: typeof grecaptcha !== "undefined",
      enterpriseAvailable:
        typeof grecaptcha !== "undefined" &&
        typeof grecaptcha.enterprise !== "undefined",
    });

    const resetMessages = () => {
      if (formErrorMessage) {
        formErrorMessage.classList.add("hidden");
        formErrorMessage.textContent = "";
      }
      if (formSuccessMessage) {
        formSuccessMessage.classList.add("hidden");
      }
    };

    const displayMessage = (type, message = "") => {
      resetMessages();
      if (type === "error" && formErrorMessage) {
        formErrorMessage.textContent = message;
        formErrorMessage.classList.remove("hidden");
      }
      if (type === "success" && formSuccessMessage) {
        formSuccessMessage.classList.remove("hidden");
      }
    };

    const setContactSubmitting = (isSubmitting) => {
      setSubmittingState(
        submitButton,
        isSubmitting,
        i18n.t("contact.form.loading", "Sender...")
      );
    };

    contactForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      resetMessages();
      setContactSubmitting(true);

      recaptchaLog("Contact form submit started");

      try {
        const recaptchaToken = await fetchRecaptchaToken("contact");
        const formData = new FormData(contactForm);
        if (recaptchaToken) {
          formData.set("recaptcha_token", recaptchaToken);
        }

        recaptchaLog("Sending POST to:", formEndpoint);
        recaptchaLog("Form data keys:", Array.from(formData.keys()));

        const response = await fetch(formEndpoint, {
          method: "POST",
          body: formData,
          headers: { Accept: "application/json" },
        });

        recaptchaLog("Response status:", response.status, response.statusText);
        recaptchaLog(
          "Response headers:",
          Object.fromEntries(response.headers.entries())
        );

        const result = await parseJsonResponse(response);
        recaptchaLog("Parsed response:", result);

        if (response.ok && result.success === true) {
          recaptchaLog("Submission succeeded");
          displayMessage("success");
          contactForm.reset();

          if (
            recaptchaSiteKey &&
            typeof grecaptcha !== "undefined" &&
            recaptchaHasClients()
          ) {
            try {
              grecaptcha.reset();
            } catch (error) {
              recaptchaError("Reset failed", error);
            }
          } else {
            recaptchaLog(
              "Skipping grecaptcha.reset() – no clients registered (expected for v3)."
            );
          }
        } else {
          const message =
            result.message ||
            i18n.t(
              "common.form_error_default",
              "Der opstod en fejl. Prøv igen senere."
            );
          recaptchaError("Submission failed", message, result);
          displayMessage("error", message);
        }
      } catch (error) {
        recaptchaError("Unexpected submission error", error);
        displayMessage(
          "error",
          i18n.t(
            "common.form_error_network",
            "Kunne ikke sende forespørgslen. Kontrollér din forbindelse og prøv igen."
          )
        );
      } finally {
        setContactSubmitting(false);
      }
    });
  }

  const calendlyPopupButtons = document.querySelectorAll(
    '[data-calendly-launch="popup"]'
  );
  if (calendlyPopupButtons.length) {
    calendlyPopupButtons.forEach((button) => {
      button.addEventListener("click", (event) => {
        event.preventDefault();
        const url = button.getAttribute("data-calendly-url");
        if (!url) {
          return;
        }
        if (
          window.Calendly &&
          typeof window.Calendly.initPopupWidget === "function"
        ) {
          window.Calendly.initPopupWidget({ url });
        } else {
          window.open(url, "_blank", "noopener");
        }
      });
    });
  }

  const scanForm = document.getElementById("vulnerability-scan-form");
  if (scanForm) {
    const domainInput = scanForm.querySelector("#scan-domain");
    const emailInput = scanForm.querySelector("#scan-email");
    const statusEl = document.getElementById("vulnerability-scan-status");
    const successCard = document.getElementById("vulnerability-scan-success");
    const resultContainer = document.getElementById(
      "vulnerability-scan-result"
    );
    const submitButton = scanForm.querySelector('button[type="submit"]');
    const endpoint =
      scanForm.dataset.endpoint ||
      scanForm.getAttribute("action") ||
      "scan-submit.php";
    const loadingMessage =
      submitButton?.dataset.loadingText ||
      i18n.t("free_scan.form.loading", "Analyserer angrebsfladen...");

    const setScanSubmitting = (isSubmitting) => {
      setSubmittingState(submitButton, isSubmitting, loadingMessage);
    };

    const resetScanOutput = () => {
      if (statusEl) {
        statusEl.classList.add("hidden");
        statusEl.textContent = "";
        statusEl.classList.remove(
          "text-rose-400",
          "text-emerald-400",
          "text-amber-300"
        );
      }
      if (successCard) {
        successCard.classList.add("hidden");
      }
      if (resultContainer) {
        resultContainer.classList.add("hidden");
        resultContainer.innerHTML = "";
      }
      clearFieldError(domainInput);
      clearFieldError(emailInput);
    };

    const updateScanStatus = (message, tone = "info") => {
      if (!statusEl) {
        return;
      }
      statusEl.textContent = message;
      statusEl.classList.remove(
        "hidden",
        "text-rose-400",
        "text-emerald-400",
        "text-amber-300"
      );
      if (tone === "error") {
        statusEl.classList.add("text-rose-400");
      } else if (tone === "success") {
        statusEl.classList.add("text-emerald-400");
      } else {
        statusEl.classList.add("text-amber-300");
      }
    };

    const renderScanReport = (report) => {
      if (!resultContainer) {
        return;
      }

      const labels = resultContainer.dataset;
      const severityLabel = (severity) => {
        if (severity === "high") return labels.labelSeverityHigh || "High risk";
        if (severity === "medium")
          return labels.labelSeverityMedium || "Medium risk";
        return labels.labelSeverityLow || "Low risk";
      };

      const severityClass = (severity) => {
        if (severity === "high")
          return "bg-red-500/20 text-red-300 border border-red-500/40";
        if (severity === "medium")
          return "bg-amber-500/20 text-amber-200 border border-amber-500/40";
        return "bg-emerald-500/20 text-emerald-200 border border-emerald-500/40";
      };

      const issues = Array.isArray(report?.issues) ? report.issues : [];
      const issuesMarkup = issues.length
        ? issues
            .map(
              (issue) => `
                    <li class="border border-gray-800/60 rounded-xl p-4 bg-gray-900/50 space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-semibold text-white">${
                              issue.title || ""
                            }</span>
                            <span class="text-xs px-2 py-1 rounded-full ${severityClass(
                              issue.severity
                            )}">
                                ${severityLabel(issue.severity || "low")}
                            </span>
                        </div>
                        <p class="text-sm text-gray-300">${
                          issue.description || ""
                        }</p>
                    </li>
                `
            )
            .join("")
        : `<li class="text-sm text-gray-300">${
            labels.labelNoIssues || "No critical findings in this mock."
          }</li>`;

      const planMap = {
        "pricing.mvp.basis.title": labels.planMvpBasis,
        "pricing.mvp.pro.title": labels.planMvpPro,
        "pricing.mvp.premium.title": labels.planMvpPremium,
        "pricing.enterprise.standard.title": labels.planStandard,
        "pricing.enterprise.premium.title": labels.planPremium,
        "pricing.enterprise.enterprise.title": labels.planEnterprise,
      };

      const recommendedPlan =
        planMap[report?.planRecommendation] ||
        planMap["pricing.enterprise.standard.title"] ||
        "";

      const scoreLabel = labels.labelScore || "Security score";
      const issuesLabel = labels.labelIssues || "Highlighted findings";
      const recommendedLabel = labels.labelRecommended || "Recommended plan";
      const complianceLabel = labels.labelCompliance || "";
      const disclaimerLabel = labels.labelDisclaimer || "";
      const nextStepsLabel = labels.labelNext || "";
      const ctaLabel = labels.labelCta || "Book a demo";
      const demoUrl = labels.demoUrl || "demo.php";

      resultContainer.innerHTML = `
                <div class="border border-gray-800/60 rounded-2xl p-6 bg-gray-900/40 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">${scoreLabel}</p>
                            <p class="text-4xl font-black text-amber-400">${
                              report?.score ?? "--"
                            }</p>
                            <p class="text-xs text-gray-500 mt-2">${complianceLabel}</p>
                        </div>
                        <div class="text-sm text-gray-300 sm:text-right">
                            <p class="font-semibold text-white">${recommendedLabel}</p>
                            <p>${recommendedPlan}</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-3 text-white">${issuesLabel}</h4>
                        <ul class="space-y-4">${issuesMarkup}</ul>
                    </div>
                    <div class="space-y-3 text-sm text-gray-300">
                        <p>${nextStepsLabel}</p>
                        <p class="text-xs text-gray-500">${disclaimerLabel}</p>
                    </div>
                    <div>
                        <a href="${demoUrl}" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-6 rounded-lg hover:bg-amber-500 transition-colors">${ctaLabel}</a>
                    </div>
                </div>
            `;
      resultContainer.classList.remove("hidden");
    };

    const validateScanForm = () => {
      let hasError = false;
      const domainValue = domainInput?.value.trim() || "";
      const domainRequiredMessage =
        domainInput?.dataset.errorMessage ||
        i18n.t("free_scan.validation.domain_required", "Indtast et domæne.");
      const domainInvalidMessage =
        domainInput?.dataset.invalidMessage ||
        i18n.t(
          "free_scan.validation.domain_invalid",
          "Angiv et gyldigt domæne (fx example.com)."
        );

      if (!domainValue) {
        showFieldError(domainInput, domainRequiredMessage);
        hasError = true;
      } else {
        const domainPattern = /^(?!-)(?:[a-z0-9-]{1,63}\.)+[a-z]{2,}$/i;
        if (!domainPattern.test(domainValue)) {
          showFieldError(domainInput, domainInvalidMessage);
          hasError = true;
        }
      }

      const emailValue = emailInput?.value.trim() || "";
      if (emailValue) {
        const emailInvalidMessage =
          emailInput?.dataset.invalidMessage ||
          i18n.t(
            "free_scan.validation.email_invalid",
            "Angiv en gyldig e-mailadresse."
          );
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailValue)) {
          showFieldError(emailInput, emailInvalidMessage);
          hasError = true;
        }
      }

      return !hasError;
    };

    domainInput?.addEventListener("input", () => {
      clearFieldError(domainInput);
      if (statusEl) {
        statusEl.classList.add("hidden");
      }
    });

    emailInput?.addEventListener("input", () => {
      clearFieldError(emailInput);
    });

    scanForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      resetScanOutput();

      if (!validateScanForm()) {
        updateScanStatus(
          i18n.t(
            "free_scan.errors.validation",
            "Kontrollér felterne og prøv igen."
          ),
          "error"
        );
        return;
      }

      updateScanStatus(loadingMessage, "info");
      setScanSubmitting(true);

      try {
        const recaptchaToken = await fetchRecaptchaToken("lead_scan");
        const formData = new FormData(scanForm);
        if (recaptchaToken) {
          formData.set("recaptcha_token", recaptchaToken);
        }

        if (resultContainer) {
          showSkeletonLoader(resultContainer);
        }

        const response = await fetch(endpoint, {
          method: "POST",
          body: formData,
          headers: { Accept: "application/json" },
        });

        const result = await parseJsonResponse(response);

        if (response.ok && result.success) {
          updateScanStatus(
            i18n.t("free_scan.form.success_status", "Mock-rapport genereret."),
            "success"
          );
          if (successCard) {
            successCard.classList.remove("hidden");
          }
          renderScanReport(result.report || {});
        } else {
          const message =
            result.message ||
            i18n.t(
              "free_scan.errors.generic",
              "Vi kunne ikke gennemføre scanningen. Prøv igen."
            );
          updateScanStatus(message, "error");
          if (result.field === "domain") {
            showFieldError(domainInput, message);
          } else if (result.field === "email") {
            showFieldError(emailInput, message);
          }
          if (resultContainer) {
            resultContainer.classList.add("hidden");
            resultContainer.innerHTML = "";
          }
        }
      } catch (error) {
        recaptchaError("Free scan submission error", error);
        updateScanStatus(
          i18n.t(
            "free_scan.errors.network",
            "Forbindelsen blev afbrudt. Prøv igen."
          ),
          "error"
        );
      } finally {
        setScanSubmitting(false);
      }
    });
  }

  const pricingCalculator = document.getElementById("pricing-calculator");
  const pricingCalculatorForm = document.getElementById(
    "pricing-calculator-form"
  );
  if (pricingCalculator && pricingCalculatorForm) {
    const usersInput = pricingCalculatorForm.querySelector("#calc-users");
    const endpointsInput =
      pricingCalculatorForm.querySelector("#calc-endpoints");
    const addonInputs = Array.from(
      pricingCalculatorForm.querySelectorAll('input[name="addons"]')
    );
    const submitButton = pricingCalculatorForm.querySelector(
      'button[type="submit"]'
    );
    const resultContainer = document.getElementById(
      "pricing-calculator-result"
    );
    const statusEl = document.getElementById("pricing-calculator-status");
    const labels = pricingCalculator.dataset;

    const planLabels = {
      mvpBasis: labels.planMvpBasis,
      mvpPro: labels.planMvpPro,
      mvpPremium: labels.planMvpPremium,
      standard: labels.planStandard,
      premium: labels.planPremium,
      enterprise: labels.planEnterprise,
    };

    const planMatrix = [
      { maxUsers: 10, slug: "mvpBasis", base: 1799 },
      { maxUsers: 25, slug: "mvpPro", base: 3499 },
      { maxUsers: 50, slug: "mvpPremium", base: 5999 },
      { maxUsers: 100, slug: "standard", base: 9900 },
      { maxUsers: 250, slug: "premium", base: 18900 },
      { maxUsers: Infinity, slug: "enterprise", base: 39900 },
    ];

    const addonPricing = {
      pve: 2900,
      aut: 1200,
      bridge: 1800,
      support: 3500,
    };

    const formatCurrency = (value) =>
      currencyFormatter.format(Math.round(value));

    const setCalculatorSubmitting = (isSubmitting) => {
      const loadingText =
        submitButton?.dataset.loadingText ||
        i18n.t("pricing.calculator.loading", "Beregner...");
      setSubmittingState(submitButton, isSubmitting, loadingText);
    };

    const showCalcStatus = (message, tone = "info") => {
      if (!statusEl) {
        return;
      }
      statusEl.textContent = message || "";
      statusEl.dataset.tone = tone;
      statusEl.hidden = !message;
    };

    const resetCalculator = () => {
      clearFieldError(usersInput);
      clearFieldError(endpointsInput);
      showCalcStatus("");
      if (resultContainer) {
        resultContainer.classList.add("hidden");
        resultContainer.innerHTML = "";
      }
    };

    const getSelectedAddonLabels = () =>
      addonInputs
        .filter((input) => input.checked)
        .map((input) => {
          const wrapper = input.nextElementSibling;
          const titleEl = wrapper?.querySelector(".font-semibold");
          return titleEl ? titleEl.textContent.trim() : input.value;
        });

    // Parse numeric input, handling spaces and thousand separators
    const parseNumericInput = (value) => {
      if (!value || typeof value !== "string") return NaN;
      // Remove spaces, dots as thousand separators, keep only digits
      const cleaned = value.replace(/[\s.]/g, "").replace(/,/g, "");
      return Number.parseInt(cleaned, 10);
    };

    const validateUsersField = () => {
      const usersValue = parseNumericInput(usersInput?.value);
      clearFieldError(usersInput);

      if (Number.isNaN(usersValue) || usersValue < 1) {
        const message =
          usersInput?.dataset.minMessage ||
          usersInput?.dataset.requiredMessage ||
          i18n.t(
            "pricing.calculator.validation.users_min",
            "Der skal være mindst 1 bruger."
          );
        showFieldError(usersInput, message);
        return false;
      } else if (usersValue > 10000) {
        showFieldError(
          usersInput,
          i18n.t(
            "pricing.calculator.validation.users_max",
            "Kontakt os direkte for over 10.000 brugere."
          )
        );
        return false;
      }
      return true;
    };

    const validateEndpointsField = () => {
      const endpointsValue = parseNumericInput(endpointsInput?.value);
      clearFieldError(endpointsInput);

      if (Number.isNaN(endpointsValue) || endpointsValue < 0) {
        const message =
          endpointsInput?.dataset.requiredMessage ||
          i18n.t(
            "pricing.calculator.validation.endpoints_required",
            "Angiv antal aktive endpoints."
          );
        showFieldError(endpointsInput, message);
        return false;
      } else if (endpointsValue > 50000) {
        showFieldError(
          endpointsInput,
          i18n.t(
            "pricing.calculator.validation.endpoints_max",
            "Kontakt os direkte for over 50.000 endpoints."
          )
        );
        return false;
      }
      return true;
    };

    const validateCalculator = () => {
      const usersValid = validateUsersField();
      const endpointsValid = validateEndpointsField();
      return usersValid && endpointsValid;
    };

    // Clear errors on input, validate on blur
    usersInput?.addEventListener("input", () => clearFieldError(usersInput));
    usersInput?.addEventListener("blur", validateUsersField);
    endpointsInput?.addEventListener("input", () =>
      clearFieldError(endpointsInput)
    );
    endpointsInput?.addEventListener("blur", validateEndpointsField);

    pricingCalculatorForm.addEventListener("reset", () => {
      window.setTimeout(resetCalculator, 0);
    });

    pricingCalculatorForm.addEventListener("submit", (event) => {
      event.preventDefault();

      // Clear previous errors and result, but keep validation state
      showCalcStatus("");
      if (resultContainer) {
        resultContainer.classList.add("hidden");
        resultContainer.innerHTML = "";
      }

      // Validate first - don't clear field errors before checking
      if (!validateCalculator()) {
        showCalcStatus(
          i18n.t(
            "pricing.calculator.validation.error",
            "Ret de markerede felter for at fortsætte."
          ),
          "error"
        );
        return;
      }

      setCalculatorSubmitting(true);

      try {
        const users = parseNumericInput(usersInput?.value) || 0;
        const endpoints = parseNumericInput(endpointsInput?.value) || 0;
        const selectedAddons = addonInputs
          .filter((input) => input.checked)
          .map((input) => input.value);

        const selectedPlan =
          planMatrix.find((plan) => users <= plan.maxUsers) ||
          planMatrix[planMatrix.length - 1];
        let total = selectedPlan.base;

        const endpointTierSize = 50;
        const endpointTierPrice = 1200;
        const extraEndpointTiers = Math.max(
          0,
          Math.ceil(endpoints / endpointTierSize) - 1
        );
        total += extraEndpointTiers * endpointTierPrice;

        selectedAddons.forEach((key) => {
          total += addonPricing[key] || 0;
        });

        const perUser = users > 0 ? total / users : total;

        const selectedAddonLabels = getSelectedAddonLabels();
        const addonsList = selectedAddonLabels.length
          ? selectedAddonLabels.map((label) => `<li>• ${label}</li>`).join("")
          : `<li>${i18n.t(
              "pricing.calculator.no_addons",
              "No add-ons selected"
            )}</li>`;

        const planLabel =
          planLabels[selectedPlan.slug] || labels.planStandard || "Standard";
        const resultHeading = labels.resultHeading || "Estimeret investering";
        const monthlyLabel = labels.resultMonthly || "Anslået pris pr. måned";
        const perUserLabel = labels.resultPerUser || "Pris pr. bruger";
        const recommendedLabel = labels.resultRecommended || "Anbefalet plan";
        const addonsLabel = labels.resultAddons || "Tilvalg";
        const complianceLabel = labels.resultCompliance || "";
        const disclaimerLabel = labels.resultDisclaimer || "";
        const nextStepsLabel = labels.resultNext || "";
        const demoLabel =
          labels.demoLabel || i18n.t("header.menu.demo", "Book demo");
        const popularBadge = labels.resultPopular || "";

        if (resultContainer) {
          resultContainer.innerHTML = `
                        <div class="border border-gray-800/60 rounded-2xl p-6 bg-gray-900/40 space-y-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-white">${resultHeading}</h3>
                                    <p class="text-sm text-gray-400">${nextStepsLabel}</p>
                                </div>
                                <div class="text-sm text-gray-300 sm:text-right">
                                    <p class="text-xs uppercase text-gray-500">${monthlyLabel}</p>
                                    <p class="text-3xl font-bold text-amber-400">${formatCurrency(
                                      total
                                    )}</p>
                                </div>
                            </div>
                            <div class="grid sm:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs uppercase text-gray-500">${perUserLabel}</p>
                                    <p class="text-lg font-semibold text-white">${formatCurrency(
                                      perUser
                                    )}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-gray-500">${recommendedLabel}</p>
                                    <p class="text-lg font-semibold text-white">${planLabel}</p>
                                    ${
                                      selectedPlan.slug === "premium"
                                        ? `<span class="inline-flex mt-2 text-xs px-2 py-1 rounded-full bg-amber-500/20 text-amber-200">${popularBadge}</span>`
                                        : ""
                                    }
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-gray-500">${addonsLabel}</p>
                                    <ul class="text-sm text-gray-300 space-y-1">${addonsList}</ul>
                                </div>
                            </div>
                            <div class="text-sm text-gray-400 space-y-2">
                                <p>${complianceLabel}</p>
                                <p class="text-xs text-gray-500">${disclaimerLabel}</p>
                            </div>
                            <div>
                                <a href="demo.php" class="inline-flex items-center justify-center bg-amber-400 text-black font-semibold py-3 px-6 rounded-lg hover:bg-amber-500 transition-colors">${demoLabel}</a>
                            </div>
                        </div>
                    `;
          resultContainer.classList.remove("hidden");
        }

        showCalcStatus(
          i18n.t("pricing.calculator.status_ready", "Estimatet er klar."),
          "success"
        );
      } finally {
        setCalculatorSubmitting(false);
      }
    });
  }

  const caseNavigatorTabs = document.querySelectorAll("[data-case-tab]");
  const caseNavigatorPanels = document.querySelectorAll("[data-case-panel]");
  if (caseNavigatorTabs.length && caseNavigatorPanels.length) {
    const activateCasePanel = (key) => {
      caseNavigatorPanels.forEach((panel) => {
        const isMatch = panel.dataset.casePanel === key;
        panel.classList.toggle("is-visible", isMatch);
        if (isMatch) {
          panel.removeAttribute("hidden");
        } else {
          panel.setAttribute("hidden", "hidden");
        }
      });

      caseNavigatorTabs.forEach((tab) => {
        const isActive = tab.dataset.caseTab === key;
        tab.classList.toggle("is-active", isActive);
        tab.setAttribute("aria-selected", isActive ? "true" : "false");
        tab.setAttribute("tabindex", isActive ? "0" : "-1");
      });
    };

    caseNavigatorTabs.forEach((tab, index) => {
      tab.addEventListener("click", () => {
        activateCasePanel(tab.dataset.caseTab || "");
      });

      tab.addEventListener("keydown", (event) => {
        const tabsArray = Array.from(caseNavigatorTabs);
        if (event.key === "ArrowRight" || event.key === "ArrowDown") {
          event.preventDefault();
          const nextTab = tabsArray[(index + 1) % tabsArray.length];
          nextTab.focus();
          activateCasePanel(nextTab.dataset.caseTab || "");
        }
        if (event.key === "ArrowLeft" || event.key === "ArrowUp") {
          event.preventDefault();
          const prevTab =
            tabsArray[(index - 1 + tabsArray.length) % tabsArray.length];
          prevTab.focus();
          activateCasePanel(prevTab.dataset.caseTab || "");
        }
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          activateCasePanel(tab.dataset.caseTab || "");
        }
      });
    });
  }

  // ==========================================
  // MATRIX RAIN ANIMATION - REMOVED
  // ==========================================
  // The Matrix rain animation has been removed as part of the
  // Graphene theme upgrade. The hero section now uses a static
  // SVG hexagon background pattern instead.
  // See PR #54 for details on the Graphene design system.

  const hasAIConfig = typeof window.AI_CONFIG !== "undefined";
  const sanitizedApiKey = hasAIConfig
    ? String(AI_CONFIG.GEMINI_API_KEY || "").trim()
    : "";
  const isPlaceholderApiKey =
    sanitizedApiKey.length === 0 ||
    /REPLACE|FAKE|DEMO|TEST/i.test(sanitizedApiKey);
  const geminiReady =
    hasAIConfig &&
    Boolean(String(AI_CONFIG.GEMINI_MODEL || "").trim()) &&
    Boolean(String(AI_CONFIG.API_BASE_URL || "").trim()) &&
    !isPlaceholderApiKey;

  const convertMarkdownToHtml = (text) => {
    let html = text
      .replace(
        /### (.*$)/gim,
        '<h3 class="text-lg font-bold mb-2 text-amber-400">$1</h3>'
      )
      .replace(
        /## (.*$)/gim,
        '<h2 class="text-xl font-bold mb-3 text-amber-400">$1</h2>'
      )
      .replace(
        /# (.*$)/gim,
        '<h1 class="text-2xl font-bold mb-4 text-amber-400">$1</h1>'
      )
      .replace(/\*\*(.*?)\*\*/g, '<strong class="text-white">$1</strong>')
      .replace(/\*(.*?)\*/g, '<em class="text-gray-200">$1</em>')
      .replace(
        /`(.*?)`/g,
        '<code class="bg-gray-800 px-1 py-0.5 rounded text-amber-300 text-sm">$1</code>'
      )
      .replace(/^\* (.*$)/gim, '<li class="ml-5 mb-2 text-gray-300">$1</li>')
      .replace(/^- (.*$)/gim, '<li class="ml-5 mb-2 text-gray-300">$1</li>')
      .replace(/\n/g, "<br>");

    html = html.replace(
      /(<li[^>]*>.*?<\/li>(?:\s*<br>\s*<li[^>]*>.*?<\/li>)*)/gs,
      '<ul class="mb-4">$1</ul>'
    );
    html = html.replace(/<br><ul>/g, "<ul>").replace(/<\/ul><br>/g, "</ul>");
    return html;
  };

  const callGemini = async (
    prompt,
    resultElement,
    loaderElement,
    requestType = "generic"
  ) => {
    if (!geminiReady) {
      if (loaderElement) loaderElement.classList.add("hidden");
      if (resultElement) {
        resultElement.innerHTML =
          '<p class="text-red-400">AI-konfiguration mangler. Kontakt administratoren.</p>';
        resultElement.classList.remove("hidden");
      }
      return;
    }

    const now = Date.now();
    const requestKey = "gemini_requests";
    const limit = AI_CONFIG.MAX_REQUESTS_PER_MINUTE || 5;
    let requests = [];

    try {
      requests = JSON.parse(localStorage.getItem(requestKey) || "[]");
    } catch (error) {
      requests = [];
    }

    requests = requests.filter((time) => now - time < 60000);
    if (requests.length >= limit) {
      if (loaderElement) loaderElement.classList.add("hidden");
      if (resultElement) {
        resultElement.innerHTML = `<p style="color: var(--text-gold);">${i18n.t(
          "common.ai_rate_limit",
          "For mange foresp\u00f8rgsler. Vent et \u00f8jeblik og pr\u00f8v igen."
        )}</p>`;
        resultElement.classList.remove("hidden");
      }
      return;
    }

    // Show improved loading state
    if (resultElement) {
      showAILoadingState(
        resultElement,
        i18n.t(
          "common.ai_loading",
          "AI-assistenten analyserer din foresp\u00f8rgsel..."
        )
      );
    }

    const payload = {
      contents: [
        {
          role: "user",
          parts: [{ text: prompt }],
        },
      ],
    };

    const apiUrl = `${AI_CONFIG.API_BASE_URL}/${AI_CONFIG.GEMINI_MODEL}:generateContent?key=${AI_CONFIG.GEMINI_API_KEY}`;
    const controller = new AbortController();
    const timeoutId = window.setTimeout(
      () => controller.abort(),
      AI_CONFIG.REQUEST_TIMEOUT || 15000
    );

    try {
      if (AI_CONFIG.LOG_REQUESTS) {
        console.info("Gemini request", { requestType, timestamp: now });
      }

      const response = await fetch(apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
        signal: controller.signal,
      });

      window.clearTimeout(timeoutId);

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`API error ${response.status}: ${errorText}`);
      }

      const result = await response.json();
      requests.push(now);
      localStorage.setItem(requestKey, JSON.stringify(requests));

      let text = i18n.t(
        "common.ai_no_response",
        "Kunne ikke generere et svar. Pr\u00f8v venligst igen."
      );
      if (result?.candidates?.[0]?.content?.parts?.[0]?.text) {
        text = result.candidates[0].content.parts[0].text;
      }

      if (resultElement) {
        resultElement.innerHTML = convertMarkdownToHtml(text);
        resultElement.classList.remove("hidden");
      }
    } catch (error) {
      if (AI_CONFIG.LOG_REQUESTS) {
        console.error("Gemini error", error);
      }
      const fallbackMessage =
        error.name === "AbortError"
          ? i18n.t(
              "common.ai_timeout",
              "Foresp\u00f8rgslen tog for lang tid \u2013 pr\u00f8v igen."
            )
          : i18n.t(
              "common.ai_error",
              "Der opstod en fejl under kommunikation med AI-assistenten."
            );
      if (resultElement) {
        resultElement.innerHTML = `<p class="text-red-400">${fallbackMessage}</p>`;
        resultElement.classList.remove("hidden");
      }
    } finally {
      if (loaderElement) loaderElement.classList.add("hidden");
    }
  };

  const quickAssessmentOutputEl = document.getElementById(
    "quick-assessment-output"
  );
  const quickAssessmentBtn = document.getElementById("quick-assessment-btn");
  if (quickAssessmentBtn) {
    const quickAssessmentInput = document.getElementById("quick-assessment");
    quickAssessmentBtn.addEventListener("click", async () => {
      if (!quickAssessmentInput?.value.trim()) {
        quickAssessmentInput?.classList.add("border-red-500");
        setTimeout(
          () => quickAssessmentInput?.classList.remove("border-red-500"),
          2500
        );
        return;
      }

      if (quickAssessmentOutputEl) {
        showAILoadingState(
          quickAssessmentOutputEl,
          i18n.t(
            "common.ai_analyzing_security",
            "Analyserer din sikkerhedssituation..."
          )
        );
      }

      const prompt = `Du er strategisk sikkerhedsrådgiver for Blackbox EYE™. Evaluer følgende udfordring og returnér tre korte afsnit: 1) Primær trussel, 2) Hurtig gevinst, 3) Foreslået Blackbox-modul. Brug et professionelt, roligt danske sprog. Kundens beskrivelse: "${quickAssessmentInput.value.trim()}".`;
      await callGemini(
        prompt,
        quickAssessmentOutputEl,
        null,
        "quick-assessment"
      );
    });
  }

  const geminiModal = document.getElementById("gemini-modal");
  const geminiTriggerBtns = document.querySelectorAll(".gemini-trigger-btn");
  if (geminiModal && geminiTriggerBtns.length) {
    const closeModalBtn = document.getElementById("close-modal-btn");
    const modalLoader = document.getElementById("modal-loader");
    const modalResult = document.getElementById("modal-result");
    const modalContent = document.getElementById("modal-content");

    let lastFocusedElement = null;

    const showModal = () => {
      lastFocusedElement = document.activeElement;
      geminiModal.classList.remove("hidden");
      // P0 FIX: NO scroll-lock - user must be able to scroll freely
      // document.body.style.overflow = "hidden"; // REMOVED - violates P0 scroll policy

      // Set focus to close button and setup focus trap
      setTimeout(() => {
        closeModalBtn?.focus();
        setupFocusTrap(modalContent);
      }, 100);
    };

    const hideModal = () => {
      geminiModal.classList.add("hidden");
      document.body.style.overflow = "";

      // Restore focus to trigger element
      if (lastFocusedElement) {
        lastFocusedElement.focus();
      }
    };

    const setupFocusTrap = (container) => {
      if (!container) return;

      const focusableElements = container.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      );

      if (focusableElements.length === 0) return;

      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];

      const handleTabKey = (e) => {
        if (e.key !== "Tab") return;

        if (e.shiftKey && document.activeElement === firstElement) {
          e.preventDefault();
          lastElement.focus();
        } else if (!e.shiftKey && document.activeElement === lastElement) {
          e.preventDefault();
          firstElement.focus();
        }
      };

      container.addEventListener("keydown", handleTabKey);
    };

    geminiTriggerBtns.forEach((btn) => {
      btn.addEventListener("click", async () => {
        const moduleName = btn.dataset.module;
        let prompt;
        if (moduleName === "PVE") {
          prompt =
            "Beskriv et realistisk cybersecurity-trusselsscenarie for en mellemstor dansk virksomhed, som Blackbox EYE's 'Penetration & Vulnerability Engine' ville opdage og forhindre. Forklar kort, hvordan en uopdaget sårbarhed i deres webshop-software kunne udnyttes af en hacker til at stjæle kundedata. Hold sproget letforståeligt for en ikke-teknisk direktør. Start med en fængende overskrift i markdown.";
        } else if (moduleName === "Blackbox EYE") {
          prompt =
            "Beskriv et realistisk insider-trusselsscenarie i en dansk kommune, som Blackbox EYE's Intelligence Module ville opdage. Scenariet skal involvere en medarbejder, der forsøger at eksfiltrere følsomme borgerdata over en længere periode ved at tilgå filservere uden for normal arbejdstid. Forklar hvordan Blackbox EYE's anomali-detektion ville reagere. Hold sproget letforståeligt. Start med en fængende overskrift i markdown.";
        } else {
          prompt =
            "Beskriv et generelt cybersecurity-trusselsscenarie til Blackbox EYE™.";
        }

        showModal();
        if (modalLoader && modalResult) {
          showAILoadingState(modalLoader);
          modalResult.innerHTML = "";
          modalResult.classList.add("hidden");
        }
        await callGemini(
          prompt,
          modalResult,
          modalLoader,
          `gemini-modal-${moduleName || "generic"}`
        );
      });
    });

    closeModalBtn?.addEventListener("click", hideModal);
    geminiModal.addEventListener("click", (event) => {
      if (event.target === geminiModal) {
        hideModal();
      }
    });

    // ESC key to close modal
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && !geminiModal.classList.contains("hidden")) {
        hideModal();
      }
    });
  }

  const recommendationBtn = document.getElementById("get-recommendation-btn");
  if (recommendationBtn) {
    const recommendationContainer = document.getElementById(
      "recommendation-result-container"
    );
    const recommendationLoader = document.getElementById(
      "recommendation-loader"
    );
    const recommendationResult = document.getElementById(
      "recommendation-result"
    );
    recommendationBtn.addEventListener("click", async () => {
      const industrySelect = document.getElementById("industry-select");
      const employeeCountInput = document.getElementById("employee-count");
      const industry =
        industrySelect && "value" in industrySelect
          ? industrySelect.value
          : "Ukendt branche";
      const employees =
        employeeCountInput && "value" in employeeCountInput
          ? employeeCountInput.value
          : "0";

      recommendationContainer?.classList.remove("hidden");
      if (recommendationLoader) showSkeletonLoader(recommendationLoader);
      if (recommendationResult) {
        recommendationResult.classList.add("hidden");
        recommendationResult.innerHTML = "";
      }

      const prompt = `Du er AI-sikkerhedsrådgiver for Blackbox EYE™. En potentiel kunde fra '${industry}'-branchen med ${employees} ansatte har bedt om en analyse. Gør følgende i markdown: 1) Top 3 trusler, 2) Økonomisk risiko-estimat, 3) Anbefalet pakke (Standard, Premium eller Enterprise) med begrundelse.`;
      await callGemini(
        prompt,
        recommendationResult,
        recommendationLoader,
        "pricing-advice"
      );
    });
  }

  const analyzeCaseBtn = document.getElementById("analyze-case-btn");
  if (analyzeCaseBtn) {
    const caseInput = document.getElementById("case-input");
    const caseContainer = document.getElementById("case-analysis-container");
    const caseLoader = document.getElementById("case-analysis-loader");
    const caseResult = document.getElementById("case-analysis-result");

    analyzeCaseBtn.addEventListener("click", async () => {
      if (!caseInput?.value.trim()) {
        caseInput?.classList.add("border-red-500");
        setTimeout(() => caseInput?.classList.remove("border-red-500"), 2500);
        return;
      }

      caseContainer?.classList.remove("hidden");
      if (caseLoader) showSkeletonLoader(caseLoader);
      if (caseResult) {
        caseResult.classList.add("hidden");
        caseResult.innerHTML = "";
      }

      const prompt = `Du er ekspert AI-sikkerhedskonsulent for Blackbox EYE™. Kunden beskriver: "${caseInput.value.trim()}". Returnér i markdown: 1) Sammenlign med én af vores cases (kommune, ejendomsselskab, vagtselskab) og forklar hvorfor. 2) Anbefal 1-2 relevante moduler (PVE, Blackbox EYE, ID-Matrix, AUT) med begrundelser. 3) Foreslå næste skridt (f.eks. demo, workshop).`;
      await callGemini(prompt, caseResult, caseLoader, "case-analysis");
    });
  }

  if (quickAssessmentOutputEl && !hasAIConfig) {
    quickAssessmentOutputEl.classList.remove("hidden");
    quickAssessmentOutputEl.innerHTML =
      '<p class="text-red-400">AI-konfiguration mangler. Kontakt administratoren.</p>';
  }

  const alphaContainer = document.getElementById("alphabot-container");
  const alphaToggleBtn = document.getElementById("alphabot-toggle-btn");
  const alphaCloseBtn = document.getElementById("alphabot-close-btn");
  const alphaOverlay = document.getElementById("alphabot-overlay");
  const alphaPanel = document.getElementById("alphabot-panel");
  const alphaStatusDot =
    alphaContainer?.querySelector(".alphabot-status-dot") ?? null;
  const alphaRail = alphaContainer?.closest(".bbx-command-rail") ?? null;
  if (alphaOverlay) {
    alphaOverlay.setAttribute("aria-hidden", "true");
  }
  const alphaMobileQuery = window.matchMedia("(max-width: 768px)");

  // P0 FIX: Body lock COMPLETELY DISABLED
  // EYE Assistant is a corner widget - users MUST be able to scroll while it's open
  const setAlphaBodyLock = (state) => {
    // DISABLED - no body lock when Alphabot opens
    // Previously: document.body.classList.add("alphabot-locked");
    document.body.classList.remove("alphabot-locked"); // Always remove, never add
  };
  // P0 FIX: Inert mechanism COMPLETELY DISABLED
  // EYE Assistant is a corner widget - page content should remain interactive
  // Users must be able to scroll and interact with page while assistant is open
  const alphaInertTargets = () => {
    return []; // Return empty - no elements should be made inert
  };
  const alphaInertState = new Map();
  const setAlphaInert = (state) => {
    // DISABLED - no inert when Alphabot opens
    // Page remains fully interactive at all times
    alphaInertState.clear();
  };

  const markAssistantUnavailable = (tooltipMessage) => {
    if (!alphaContainer) {
      return;
    }
    alphaContainer.classList.add("alphabot-offline");
    if (alphaToggleBtn && tooltipMessage) {
      alphaToggleBtn.setAttribute("title", tooltipMessage);
    }
    if (alphaStatusDot) {
      alphaStatusDot.setAttribute("data-status", "offline");
    }
  };

  if (alphaContainer && (!geminiReady || !hasAIConfig)) {
    markAssistantUnavailable(
      i18n.t(
        "alphabot.offline_tooltip",
        "Blackbox EYE Assistant er offline. Kontakt support for at aktivere integrationen."
      )
    );
  }

  if (alphaContainer && alphaToggleBtn && alphaPanel) {
    const messagesDiv = document.getElementById("alphabot-messages");
    const inputEl = document.getElementById("alphabot-input");
    const sendBtn = document.getElementById("alphabot-send-btn");
    const sendText = document.getElementById("send-text");
    const sendLoader = document.getElementById("send-loader");

    const assistantReady = Boolean(hasAIConfig && geminiReady);

    if (messagesDiv && inputEl && sendBtn && sendText && sendLoader) {
      const conversation = [
        {
          role: "user",
          parts: [{ text: AI_CONFIG.ALPHABOT_SYSTEM_PROMPT || "" }],
        },
        {
          role: "model",
          parts: [
            {
              text: "Forstået. Jeg er klar til at assistere med sikkerhedsrelaterede spørgsmål og analyser.",
            },
          ],
        },
      ];
      let isProcessing = false;
      let alphaFocusTrapListener = null;
      let alphaFocusContainListener = null;
      let alphaLastFocusedElement = null;
      let alphaIsOpen = false;

      const alphaFocusableSelector =
        'a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])';

      const getAlphaFocusableElements = () => {
        return Array.from(
          alphaPanel.querySelectorAll(alphaFocusableSelector)
        ).filter(
          (element) =>
            element instanceof HTMLElement &&
            !element.hasAttribute("disabled") &&
            element.getAttribute("aria-hidden") !== "true"
        );
      };

      const appendMessage = (role, text) => {
        const wrapper = document.createElement("div");
        wrapper.className = `chat-message ${role}`;
        const message = document.createElement("div");
        message.className = "message-text";
        if (role === "bot") {
          message.innerHTML = convertMarkdownToHtml(text);
        } else {
          message.textContent = text;
        }
        wrapper.appendChild(message);
        messagesDiv.appendChild(wrapper);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
      };

      if (!messagesDiv.dataset.initialized) {
        const introMessage = assistantReady
          ? conversation[1]?.parts?.[0]?.text
          : i18n.t(
              "alphabot.offline_tooltip",
              "Blackbox EYE Assistant er offline. Kontakt support for at aktivere integrationen."
            );
        if (introMessage) {
          appendMessage("bot", String(introMessage).trim());
        }
        messagesDiv.dataset.initialized = "true";
      }

      const enableAlphaFocusTrap = () => {
        disableAlphaFocusTrap();
        alphaFocusTrapListener = (event) => {
          if (!alphaIsOpen || event.key !== "Tab") {
            return;
          }

          const focusable = getAlphaFocusableElements();
          if (!focusable.length) {
            return;
          }

          const first = focusable[0];
          const last = focusable[focusable.length - 1];
          const activeElement = document.activeElement;

          if (event.shiftKey) {
            if (
              activeElement === first ||
              !alphaPanel.contains(activeElement)
            ) {
              event.preventDefault();
              last.focus();
            }
          } else if (
            activeElement === last ||
            !alphaPanel.contains(activeElement)
          ) {
            event.preventDefault();
            first.focus();
          }
        };

        alphaFocusContainListener = (event) => {
          if (!alphaIsOpen) {
            return;
          }

          if (!alphaPanel.contains(event.target)) {
            const focusable = getAlphaFocusableElements();
            if (focusable.length) {
              focusable[0].focus();
            } else {
              alphaPanel.focus();
            }
          }
        };

        document.addEventListener("keydown", alphaFocusTrapListener, true);
        document.addEventListener("focusin", alphaFocusContainListener, true);
      };

      const disableAlphaFocusTrap = () => {
        if (alphaFocusTrapListener) {
          document.removeEventListener("keydown", alphaFocusTrapListener, true);
          alphaFocusTrapListener = null;
        }
        if (alphaFocusContainListener) {
          document.removeEventListener(
            "focusin",
            alphaFocusContainListener,
            true
          );
          alphaFocusContainListener = null;
        }
      };

      const setProcessing = (state) => {
        isProcessing = state;
        if (sendBtn && inputEl) {
          inputEl.disabled = state;
          sendBtn.disabled = state || !inputEl.value.trim();
        }
        if (state) {
          sendText.classList.add("hidden");
          sendLoader.classList.remove("hidden");
        } else {
          sendText.classList.remove("hidden");
          sendLoader.classList.add("hidden");
        }
      };

      const callAssistant = async () => {
        const apiUrl = `${AI_CONFIG.API_BASE_URL}/${AI_CONFIG.GEMINI_MODEL}:generateContent?key=${AI_CONFIG.GEMINI_API_KEY}`;
        const controller = new AbortController();
        const timeoutId = window.setTimeout(
          () => controller.abort(),
          AI_CONFIG.REQUEST_TIMEOUT || 15000
        );
        try {
          const response = await fetch(apiUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ contents: conversation }),
            signal: controller.signal,
          });
          window.clearTimeout(timeoutId);
          if (!response.ok) {
            const errorText = await response.text();
            throw new Error(
              `Blackbox EYE Assistant API error: ${response.status} - ${errorText}`
            );
          }
          const result = await response.json();
          const reply =
            result?.candidates?.[0]?.content?.parts?.[0]?.text ||
            i18n.t(
              "common.alphabot_error",
              "Undskyld, jeg kunne ikke generere et svar."
            );
          appendMessage("bot", reply.trim());
          conversation.push({ role: "model", parts: [{ text: reply.trim() }] });
        } catch (error) {
          const fallback =
            error.name === "AbortError"
              ? i18n.t(
                  "common.ai_timeout",
                  "Foresp\u00f8rgslen tog for lang tid \u2013 pr\u00f8v igen."
                )
              : i18n.t(
                  "common.alphabot_connection_error",
                  "Der opstod en fejl under forbindelsen til Blackbox EYE Assistant. Pr\u00f8v igen senere."
                );
          appendMessage("bot", fallback);
          if (error && error.name !== "AbortError") {
            markAssistantUnavailable(
              i18n.t(
                "alphabot.offline_tooltip",
                "Blackbox EYE Assistant er offline. Kontakt support for at aktivere integrationen."
              )
            );
          }
        } finally {
          setProcessing(false);
        }
      };

      const sendMessage = () => {
        if (isProcessing) return;
        if (!assistantReady) {
          appendMessage(
            "bot",
            i18n.t(
              "alphabot.offline_tooltip",
              "Blackbox EYE Assistant er offline. Kontakt support for at aktivere integrationen."
            )
          );
          return;
        }
        const value = inputEl.value.trim();
        if (!value) return;
        appendMessage("user", value);
        conversation.push({ role: "user", parts: [{ text: value }] });
        inputEl.value = "";
        setProcessing(true);
        void callAssistant();
      };

      const openAssistant = () => {
        alphaLastFocusedElement =
          document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;
        alphaContainer.classList.add("open");
        alphaToggleBtn.setAttribute("aria-expanded", "true");
        // P0 FIX: Overlay DISABLED - no page darkening
        // alphaOverlay?.classList.add("visible"); // REMOVED
        // alphaOverlay?.setAttribute("aria-hidden", "false"); // REMOVED
        alphaPanel.setAttribute("aria-hidden", "false");
        alphaPanel.removeAttribute("inert");
        alphaPanel.setAttribute("aria-modal", "false"); // Not a modal - it's a widget
        // P0 FIX: NO body lock - users must be able to scroll
        // setAlphaBodyLock(true); // REMOVED
        // setAlphaInert(true); // REMOVED
        alphaIsOpen = true;
        enableAlphaFocusTrap();
        if (assistantReady) {
          inputEl.focus();
        } else {
          alphaCloseBtn?.focus();
        }
      };

      const closeAssistant = (focusToggle = true) => {
        alphaContainer.classList.remove("open");
        alphaToggleBtn.setAttribute("aria-expanded", "false");
        // P0 FIX: Overlay DISABLED - nothing to remove
        // alphaOverlay?.classList.remove("visible"); // REMOVED
        // alphaOverlay?.setAttribute("aria-hidden", "true"); // REMOVED
        alphaPanel.setAttribute("aria-hidden", "true");
        alphaPanel.setAttribute("inert", "");
        alphaPanel.setAttribute("aria-modal", "false");
        // P0 FIX: NO body lock to remove
        // setAlphaBodyLock(false); // REMOVED
        // setAlphaInert(false); // REMOVED
        alphaIsOpen = false;
        disableAlphaFocusTrap();
        if (focusToggle) {
          const focusTarget =
            alphaLastFocusedElement &&
            alphaLastFocusedElement instanceof HTMLElement
              ? alphaLastFocusedElement
              : alphaToggleBtn;
          focusTarget?.focus();
        }
        alphaLastFocusedElement = null;
      };

      alphaToggleBtn.addEventListener("click", () => {
        if (alphaContainer.classList.contains("open")) {
          closeAssistant(false);
        } else {
          openAssistant();
        }
      });

      alphaCloseBtn?.addEventListener("click", () => {
        closeAssistant();
      });

      // P0 FIX: Overlay click handler DISABLED - overlay doesn't exist
      // alphaOverlay?.addEventListener("click", () => closeAssistant()); // REMOVED

      document.addEventListener("click", (event) => {
        if (
          !alphaContainer.contains(event.target) &&
          alphaContainer.classList.contains("open")
        ) {
          closeAssistant(false);
        }
      });

      document.addEventListener("keydown", (event) => {
        if (
          event.key === "Escape" &&
          alphaContainer.classList.contains("open")
        ) {
          event.preventDefault();
          closeAssistant();
        }
      });

      alphaMobileQuery.addEventListener?.("change", () => {
        if (!alphaContainer.classList.contains("open")) {
          document.body.classList.remove("alphabot-locked");
        }
      });

      // Offline mode: keep UI usable, disable sending.
      if (!assistantReady) {
        inputEl.disabled = true;
        sendBtn.disabled = true;
      }

      sendBtn.addEventListener("click", sendMessage);
      inputEl.addEventListener("keydown", (event) => {
        if (event.key === "Enter" && !event.shiftKey) {
          event.preventDefault();
          sendMessage();
        }
      });

      inputEl.addEventListener("input", () => {
        if (!isProcessing) {
          sendBtn.disabled = !inputEl.value.trim();
        }
      });
    }
  }

  // Hide assistant widget when footer is visible (prevents overlap with footer content)
  const assistantRail = document.querySelector(".bbx-command-rail");
  const siteFooter = document.querySelector("footer, .site-footer");
  if (assistantRail && siteFooter) {
    const assistantFooterObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            // Footer visible - hide assistant toggle
            assistantRail.style.opacity = "0";
            assistantRail.style.pointerEvents = "none";
            assistantRail.style.transform = "translateY(20px)";
          } else {
            // Footer not visible - show assistant toggle
            assistantRail.style.opacity = "";
            assistantRail.style.pointerEvents = "";
            assistantRail.style.transform = "";
          }
        });
      },
      { threshold: 0.1 }
    );

    assistantFooterObserver.observe(siteFooter);
  }

  // ==========================================
  // GRAPHENE MODE TOGGLE
  // ==========================================
  const grapheneToggleBtn = document.getElementById("graphene-mode-toggle");
  if (grapheneToggleBtn) {
    const updateGrapheneUI = (mode, immediate = false) => {
      const body = document.body;
      const isStrong = mode === "strong";

      // Update body classes
      body.classList.remove("graphene-standard", "graphene-strong");
      body.classList.add(isStrong ? "graphene-strong" : "graphene-standard");
      body.dataset.grapheneMode = mode;

      // Update toggle button state
      grapheneToggleBtn.setAttribute(
        "aria-pressed",
        isStrong ? "true" : "false"
      );
      grapheneToggleBtn.dataset.currentMode = mode;

      // Update toggle text if present
      const toggleText = grapheneToggleBtn.querySelector(
        ".graphene-toggle__text"
      );
      if (toggleText) {
        toggleText.textContent = isStrong
          ? window.i18n?.t("header.graphene.strong") || "Stærk"
          : window.i18n?.t("header.graphene.standard") || "Standard";
      }

      // Update title/tooltip
      grapheneToggleBtn.title = isStrong
        ? window.i18n?.t("header.graphene.mode_strong") || "Graphene Strong"
        : window.i18n?.t("header.graphene.mode_standard") ||
          "Graphene Standard";
    };

    const toggleGrapheneMode = async () => {
      const currentMode = grapheneToggleBtn.dataset.currentMode || "standard";
      const newMode = currentMode === "strong" ? "standard" : "strong";

      // Add loading state
      grapheneToggleBtn.classList.add("is-loading");
      grapheneToggleBtn.disabled = true;

      try {
        const response = await fetch("/api/graphene-toggle.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ mode: newMode }),
        });

        const data = await response.json();

        if (data.success) {
          // Update UI immediately
          updateGrapheneUI(newMode);

          // Apply new CSS variables if provided
          if (data.css_vars) {
            let styleEl = document.getElementById("graphene-dynamic-vars");
            if (!styleEl) {
              styleEl = document.createElement("style");
              styleEl.id = "graphene-dynamic-vars";
              document.head.appendChild(styleEl);
            }
            styleEl.textContent = data.css_vars;
          }

          // Store preference in localStorage for faster initial load
          try {
            localStorage.setItem("bbx-graphene-mode", newMode);
          } catch (e) {
            // Ignore localStorage errors
          }
        } else {
          console.error("Failed to toggle Graphene mode:", data.error);
        }
      } catch (error) {
        console.error("Error toggling Graphene mode:", error);
      } finally {
        // Remove loading state
        grapheneToggleBtn.classList.remove("is-loading");
        grapheneToggleBtn.disabled = false;
      }
    };

    grapheneToggleBtn.addEventListener("click", toggleGrapheneMode);

    // Initialize from localStorage if available (faster than waiting for server)
    try {
      const storedMode = localStorage.getItem("bbx-graphene-mode");
      if (
        storedMode &&
        (storedMode === "standard" || storedMode === "strong")
      ) {
        const serverMode = grapheneToggleBtn.dataset.currentMode;
        if (storedMode !== serverMode) {
          // Sync localStorage with server state
          localStorage.setItem("bbx-graphene-mode", serverMode);
        }
      }
    } catch (e) {
      // Ignore localStorage errors
    }
  }
});
