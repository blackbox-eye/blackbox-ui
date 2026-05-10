// @ts-check
const { test, expect } = require("@playwright/test");

const BASE_URL = process.env.BASE_URL || "http://localhost:8000";

const ROUTES = [
  "/",
  "/about.php",
  "/products.php",
  "/cases.php",
  "/pricing.php",
  "/contact.php",
  "/free-scan.php",
  "/demo.php",
  "/faq.php",
];

const VIEWPORTS = [
  { label: "390x844", width: 390, height: 844 },
  { label: "430x932", width: 430, height: 932 },
  { label: "768x1024", width: 768, height: 1024 },
  { label: "834x1112", width: 834, height: 1112 },
  { label: "1024x768", width: 1024, height: 768 },
  { label: "1280x900", width: 1280, height: 900 },
  { label: "1440x900", width: 1440, height: 900 },
];

const DRAWER_VIEWPORTS = VIEWPORTS.filter(({ width }) => width <= 834);
const LABEL_VIEWPORTS = VIEWPORTS.filter(({ width }) =>
  [768, 1280, 1440].includes(width),
);
const COMPACT_PANEL_VIEWPORTS = VIEWPORTS.filter(({ width }) =>
  [390, 430, 768, 834].includes(width),
);

async function openRoute(page, path, viewport) {
  await page.setViewportSize({
    width: viewport.width,
    height: viewport.height,
  });

  const response = await page.goto(`${BASE_URL}${path}`, {
    waitUntil: "domcontentloaded",
  });

  expect(response?.status() ?? 0).toBeGreaterThan(0);
  expect(response?.status() ?? 500).toBeLessThan(500);

  await page.waitForLoadState("networkidle", { timeout: 5000 }).catch(() => {});
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.locator("#alphabot-toggle-btn").waitFor({
    state: "visible",
    timeout: 10000,
  });
}

async function readAssistantState(page) {
  return page.evaluate(() => {
    const toRect = (element) => {
      if (!element) {
        return null;
      }

      const rect = element.getBoundingClientRect();
      return {
        left: rect.left,
        top: rect.top,
        right: rect.right,
        bottom: rect.bottom,
        width: rect.width,
        height: rect.height,
      };
    };

    const styleOf = (element) =>
      element ? window.getComputedStyle(element) : null;

    const rail = document.querySelector(".bbx-command-rail");
    const widget = document.getElementById("alphabot-container");
    const toggle = document.getElementById("alphabot-toggle-btn");
    const panel = document.getElementById("alphabot-panel");
    const label = toggle?.querySelector(".alphabot-label");
    const input = document.getElementById("alphabot-input");
    const sendButton = document.getElementById("alphabot-send-btn");

    return {
      viewport: {
        width: window.innerWidth,
        height: window.innerHeight,
      },
      docOverflowX: document.documentElement.scrollWidth - window.innerWidth,
      railDisplay: styleOf(rail)?.display ?? null,
      railPointerEvents: styleOf(rail)?.pointerEvents ?? null,
      widgetDisplay: styleOf(widget)?.display ?? null,
      widgetPointerEvents: styleOf(widget)?.pointerEvents ?? null,
      toggleDisplay: styleOf(toggle)?.display ?? null,
      togglePointerEvents: styleOf(toggle)?.pointerEvents ?? null,
      panelDisplay: styleOf(panel)?.display ?? null,
      panelPointerEvents: styleOf(panel)?.pointerEvents ?? null,
      panelVisibility: styleOf(panel)?.visibility ?? null,
      panelOpacity: styleOf(panel)?.opacity ?? null,
      panelAriaHidden: panel?.getAttribute("aria-hidden") ?? null,
      assistantOpen: widget?.classList.contains("open") ?? false,
      labelDisplay: styleOf(label)?.display ?? null,
      labelText: label?.textContent?.trim() ?? null,
      labelMode: toggle?.dataset.assistantLabelMode ?? null,
      toggleClientWidth: toggle?.clientWidth ?? 0,
      toggleScrollWidth: toggle?.scrollWidth ?? 0,
      panelPaddingInline: panel
        ? parseFloat(styleOf(panel)?.paddingLeft ?? "0")
        : 0,
      panelPaddingBlock: panel
        ? parseFloat(styleOf(panel)?.paddingTop ?? "0")
        : 0,
      panelTitleFontSize: panel
        ? parseFloat(
            styleOf(panel.querySelector(".alphabot-panel-title"))?.fontSize ??
              "0",
          )
        : 0,
      inputFontSize: input ? parseFloat(styleOf(input)?.fontSize ?? "0") : 0,
      sendFontSize: sendButton
        ? parseFloat(styleOf(sendButton)?.fontSize ?? "0")
        : 0,
      railRect: toRect(rail),
      widgetRect: toRect(widget),
      toggleRect: toRect(toggle),
      panelRect: toRect(panel),
      inputRect: toRect(input),
      sendRect: toRect(sendButton),
    };
  });
}

function compactPanelWidthLimit(viewport) {
  if (viewport.width <= 430) {
    return 304;
  }

  if (viewport.width <= 768) {
    return 320;
  }

  return 344;
}

async function openMobileDrawer(page) {
  const burgerButton = page.locator("#mobile-menu-button");
  const drawer = page.locator("#mobile-menu");
  const overlay = page.locator("#mobile-menu-overlay");

  await expect(burgerButton).toBeVisible();
  await burgerButton.click();
  await expect(drawer).toHaveAttribute("aria-hidden", "false");
  await expect(overlay).toHaveAttribute("aria-hidden", "false");
  await expect(page.locator("body")).toHaveClass(/mobile-menu-open/);
  await expect(page.locator("html")).toHaveClass(/mobile-menu-open/);

  return { burgerButton, drawer };
}

async function expectAssistantSuppressedForDrawer(page) {
  await expect(page.locator(".bbx-command-rail")).toBeHidden();
  await expect(page.locator("#alphabot-toggle-btn")).toBeHidden();

  const state = await readAssistantState(page);
  expect(state.docOverflowX).toBeLessThanOrEqual(1);
  expect(state.railDisplay).toBe("none");
  expect(state.widgetDisplay).toBe("none");
  expect(state.toggleDisplay).toBe("none");
  expect(state.panelAriaHidden).toBe("true");
  expect(state.panelDisplay).toBe("none");
  expect(state.panelPointerEvents).toBe("none");
  expect(state.assistantOpen).toBeFalsy();
}

async function expectDrawerControlClickable(page, selector) {
  const control = page.locator(selector);
  await expect(control).toBeVisible();
  await control.click({ trial: true });
}

async function readClosedLeakProbe(page) {
  return page.evaluate(() => {
    const toggle = document.getElementById("alphabot-toggle-btn");
    if (!toggle) {
      return [];
    }

    const rect = toggle.getBoundingClientRect();
    const points = [
      {
        name: "left-of-toggle",
        x: Math.max(0, Math.floor(rect.left) - 6),
        y: Math.min(
          window.innerHeight - 1,
          Math.max(0, Math.floor(rect.top + rect.height / 2)),
        ),
      },
      {
        name: "above-toggle",
        x: Math.min(
          window.innerWidth - 1,
          Math.max(0, Math.floor(rect.left + rect.width / 2)),
        ),
        y: Math.max(0, Math.floor(rect.top) - 6),
      },
    ];

    return points.map((point) => {
      const hit = document.elementFromPoint(point.x, point.y);
      return {
        name: point.name,
        assistantHit: Boolean(
          hit &&
          hit.closest(
            ".bbx-command-rail, #alphabot-container, #alphabot-toggle-btn, #alphabot-panel",
          ),
        ),
      };
    });
  });
}

function expectRectInsideViewport(rect, viewport) {
  expect(rect).not.toBeNull();
  expect(rect.width).toBeGreaterThan(0);
  expect(rect.height).toBeGreaterThan(0);
  expect(rect.left).toBeGreaterThanOrEqual(-1);
  expect(rect.top).toBeGreaterThanOrEqual(-1);
  expect(rect.right).toBeLessThanOrEqual(viewport.width + 1);
  expect(rect.bottom).toBeLessThanOrEqual(viewport.height + 1);
}

test.describe("Assistant responsive contract", () => {
  for (const path of ROUTES) {
    for (const viewport of VIEWPORTS) {
      test(`closed assistant stays inside viewport on ${path} @ ${viewport.label}`, async ({
        page,
      }) => {
        await openRoute(page, path, viewport);

        const state = await readAssistantState(page);
        expect(state.docOverflowX).toBeLessThanOrEqual(1);
        expect(state.railPointerEvents).toBe("none");
        expect(state.widgetPointerEvents).toBe("none");
        expect(state.togglePointerEvents).toBe("auto");
        expect(state.panelAriaHidden).toBe("true");
        expect(state.panelPointerEvents).toBe("none");
        expect(state.panelVisibility).toBe("hidden");
        expect(Number.parseFloat(state.panelOpacity)).toBe(0);

        expectRectInsideViewport(state.toggleRect, viewport);
        expectRectInsideViewport(state.widgetRect, viewport);

        const leakProbe = await readClosedLeakProbe(page);
        for (const probe of leakProbe) {
          expect(
            probe.assistantHit,
            `${probe.name} should not hit the Assistant`,
          ).toBeFalsy();
        }
      });
    }
  }

  for (const viewport of VIEWPORTS) {
    test(`open assistant panel fits viewport on / @ ${viewport.label}`, async ({
      page,
    }) => {
      await openRoute(page, "/", viewport);

      const toggle = page.locator("#alphabot-toggle-btn");
      await toggle.click();
      await page.waitForTimeout(200);

      const state = await readAssistantState(page);
      expect(state.docOverflowX).toBeLessThanOrEqual(1);
      expect(state.panelAriaHidden).toBe("false");
      expect(state.panelPointerEvents).toBe("auto");
      expect(state.panelVisibility).toBe("visible");
      expect(Number.parseFloat(state.panelOpacity)).toBeGreaterThan(0.95);
      expectRectInsideViewport(state.panelRect, viewport);
    });
  }

  for (const viewport of COMPACT_PANEL_VIEWPORTS) {
    test(`open assistant panel stays compact on /faq.php @ ${viewport.label}`, async ({
      page,
    }) => {
      await openRoute(page, "/faq.php", viewport);

      const toggle = page.locator("#alphabot-toggle-btn");
      await toggle.click();
      await page.waitForTimeout(200);

      const state = await readAssistantState(page);
      expect(state.docOverflowX).toBeLessThanOrEqual(1);
      expectRectInsideViewport(state.panelRect, viewport);
      expect(state.panelRect.width).toBeLessThanOrEqual(
        compactPanelWidthLimit(viewport),
      );
      expect(state.panelPaddingInline).toBeLessThanOrEqual(16);
      expect(state.panelPaddingBlock).toBeLessThanOrEqual(16);
      expect(state.panelTitleFontSize).toBeLessThanOrEqual(15);
      expect(state.inputFontSize).toBeLessThanOrEqual(14);
      expect(state.sendFontSize).toBeLessThanOrEqual(14);
      expect(state.inputRect.bottom).toBeLessThanOrEqual(
        state.panelRect.bottom - 8,
      );
      expect(state.sendRect.bottom).toBeLessThanOrEqual(
        state.panelRect.bottom - 8,
      );
    });
  }

  for (const viewport of LABEL_VIEWPORTS) {
    test(`closed assistant label is not clipped on /products.php @ ${viewport.label}`, async ({
      page,
    }) => {
      await openRoute(page, "/products.php", viewport);

      const state = await readAssistantState(page);
      expect(state.docOverflowX).toBeLessThanOrEqual(1);
      expect(state.labelDisplay).not.toBe("none");
      expect(state.labelText?.length ?? 0).toBeGreaterThan(0);
      expect(state.toggleScrollWidth).toBeLessThanOrEqual(
        state.toggleClientWidth + 1,
      );

      if (viewport.width >= 1440) {
        expect(state.labelMode).toBe("full");
      } else {
        expect(state.labelMode).toBe("short");
      }
    });
  }

  for (const viewport of DRAWER_VIEWPORTS) {
    test(`mobile drawer open suppresses assistant on / @ ${viewport.label}`, async ({
      page,
    }) => {
      await openRoute(page, "/", viewport);
      await openMobileDrawer(page);
      await expectAssistantSuppressedForDrawer(page);
    });
  }

  for (const viewport of DRAWER_VIEWPORTS) {
    test(`opening drawer closes assistant panel on / @ ${viewport.label}`, async ({
      page,
    }) => {
      await openRoute(page, "/", viewport);

      const toggle = page.locator("#alphabot-toggle-btn");
      await toggle.click();
      await expect(page.locator("#alphabot-panel")).toBeVisible();

      await openMobileDrawer(page);
      await expectAssistantSuppressedForDrawer(page);
    });
  }

  for (const viewport of DRAWER_VIEWPORTS) {
    test(`drawer footer controls remain clear of assistant on /about.php @ ${viewport.label}`, async ({
      page,
    }) => {
      await openRoute(page, "/about.php", viewport);
      await openMobileDrawer(page);
      await expectAssistantSuppressedForDrawer(page);

      const selectors = [
        "#mobile-menu a[data-lang-target='da']",
        "#mobile-menu button[data-theme-toggle]",
        "#mobile-menu a[data-testid='mobile-login-ccs']",
        "#mobile-menu .mobile-primary-ctas a.header-cta--primary[href='demo.php']",
        "#mobile-menu .mobile-primary-ctas a.header-cta--secondary[href='free-scan.php']",
      ];

      for (const selector of selectors) {
        await expectDrawerControlClickable(page, selector);
      }
    });
  }

  test("closed assistant does not block the primary CTA on / @ 390x844", async ({
    page,
  }) => {
    await openRoute(page, "/", VIEWPORTS[0]);

    const cta = page.locator("main a[href='demo.php']").first();
    await expect(cta).toBeVisible();
    await cta.click();
    await expect(page).toHaveURL(/\/demo\.php$/);
  });

  test("drawer Book Demo CTA remains clickable on /about.php @ 390x844", async ({
    page,
  }) => {
    await openRoute(page, "/about.php", VIEWPORTS[0]);

    await openMobileDrawer(page);
    await expectAssistantSuppressedForDrawer(page);

    const demoLink = page.locator(
      "#mobile-menu .mobile-primary-ctas a.header-cta--primary[href='demo.php']",
    );
    await expect(demoLink).toBeVisible();
    await demoLink.click();
    await expect(page).toHaveURL(/\/demo\.php$/);
  });
});
