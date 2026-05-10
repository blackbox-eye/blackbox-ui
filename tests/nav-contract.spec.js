// @ts-check
const { test, expect } = require("@playwright/test");

const BASE_URL = process.env.BASE_URL || "http://localhost:8000";

const ROUTES = [
  "/",
  "/products.php",
  "/contact.php",
  "/free-scan.php",
  "/demo.php",
  "/faq.php",
];

async function openRoute(page, path, viewport) {
  await page.setViewportSize(viewport);
  const response = await page.goto(`${BASE_URL}${path}`, {
    waitUntil: "domcontentloaded",
  });

  expect(response?.status() ?? 0).toBeGreaterThan(0);
  expect(response?.status() ?? 500).toBeLessThan(500);
  await page.waitForTimeout(500);
}

async function clickDrawerBackdrop(page) {
  await page.mouse.click(20, 200);
}

async function clickDesktopOutsideMenu(page) {
  await page.mouse.click(40, 160);
}

async function readScrollContract(page) {
  return page.evaluate(() => {
    const main = document.getElementById("main-content");
    const rail = document.querySelector(".bbx-command-rail");
    const panel = document.getElementById("alphabot-panel");

    return {
      mainOverflowX: main ? getComputedStyle(main).overflowX : null,
      mainOverflowY: main ? getComputedStyle(main).overflowY : null,
      railPointerEvents: rail ? getComputedStyle(rail).pointerEvents : null,
      panelAriaHidden: panel ? panel.getAttribute("aria-hidden") : null,
      panelPointerEvents: panel ? getComputedStyle(panel).pointerEvents : null,
      panelVisibility: panel ? getComputedStyle(panel).visibility : null,
      panelOpacity: panel ? getComputedStyle(panel).opacity : null,
    };
  });
}

async function resetScrollProbeState(page) {
  await page.evaluate(() => {
    const root = document.documentElement;
    const body = document.body;
    const main = document.getElementById("main-content");

    root.style.scrollBehavior = "auto";
    body.style.scrollBehavior = "auto";

    window.scrollTo(0, 0);
    root.scrollTop = 0;
    body.scrollTop = 0;

    if (main) {
      main.scrollTop = 0;
    }
  });

  await page.waitForFunction(() => {
    const main = document.getElementById("main-content");

    return (
      Math.abs(window.scrollY) < 1 &&
      Math.abs(document.documentElement.scrollTop) < 1 &&
      Math.abs(document.body.scrollTop) < 1 &&
      (!main || Math.abs(main.scrollTop) < 1)
    );
  });
}

async function wheelProbe(
  page,
  viewport,
  point = { xRatio: 0.5, yRatio: 0.65 },
) {
  const x = Math.round(viewport.width * point.xRatio);
  const y = Math.round(viewport.height * point.yRatio);

  await resetScrollProbeState(page);

  const before = await page.evaluate(() => {
    const main = document.getElementById("main-content");
    return {
      windowY: window.scrollY,
      htmlTop: document.documentElement.scrollTop,
      bodyTop: document.body.scrollTop,
      mainTop: main ? main.scrollTop : 0,
    };
  });

  await page.mouse.move(x, y);
  await page.mouse.wheel(0, 700);
  await page.waitForTimeout(250);

  const after = await page.evaluate(() => {
    const main = document.getElementById("main-content");
    return {
      windowY: window.scrollY,
      htmlTop: document.documentElement.scrollTop,
      bodyTop: document.body.scrollTop,
      mainTop: main ? main.scrollTop : 0,
    };
  });

  return {
    deltaWindow: after.windowY - before.windowY,
    deltaHtml: after.htmlTop - before.htmlTop,
    deltaBody: after.bodyTop - before.bodyTop,
    deltaMain: after.mainTop - before.mainTop,
  };
}

function expectClosedAssistantContract(contract) {
  expect(contract.railPointerEvents).toBe("none");
  expect(contract.panelAriaHidden).toBe("true");
  expect(contract.panelPointerEvents).toBe("none");
  expect(contract.panelVisibility).toBe("hidden");
  expect(Number.parseFloat(contract.panelOpacity)).toBe(0);
}

async function expectDocumentOwnedScroll(page, viewport) {
  const contract = await readScrollContract(page);

  expect(contract.mainOverflowX).toBe("clip");
  expect(contract.mainOverflowY).toBe("visible");
  expectClosedAssistantContract(contract);

  const probe = await wheelProbe(page, viewport);
  expect(Math.max(probe.deltaWindow, probe.deltaHtml)).toBeGreaterThan(0);
  expect(Math.abs(probe.deltaBody)).toBeLessThan(1);
  expect(Math.abs(probe.deltaMain)).toBeLessThan(1);
}

test.describe("Header Navigation Contract", () => {
  for (const path of ROUTES) {
    test(`mobile drawer opens and closes on ${path}`, async ({ page }) => {
      const viewport = { width: 390, height: 844 };

      await openRoute(page, path, viewport);

      const burgerButton = page.locator("#mobile-menu-button");
      const drawer = page.locator("#mobile-menu");
      const overlay = page.locator("#mobile-menu-overlay");
      const closeButton = page.locator("#mobile-menu-close");
      const faqLink = page.locator("#mobile-menu a[href='faq.php']");

      await expect(burgerButton).toBeVisible();
      await expect(drawer).toHaveAttribute("aria-hidden", "true");

      await burgerButton.click();
      await expect(burgerButton).toHaveAttribute("aria-expanded", "true");
      await expect(drawer).toHaveAttribute("aria-hidden", "false");
      await expect(drawer).toBeVisible();
      await expect(overlay).toHaveAttribute("aria-hidden", "false");
      await expect(overlay).toBeVisible();
      await expect(closeButton).toBeVisible();
      await expect(faqLink).toBeVisible();

      await clickDrawerBackdrop(page);
      await page.waitForTimeout(300);
      await expect(burgerButton).toHaveAttribute("aria-expanded", "false");
      await expect(drawer).toHaveAttribute("aria-hidden", "true");

      await burgerButton.click();
      await expect(drawer).toHaveAttribute("aria-hidden", "false");
      await closeButton.click();
      await page.waitForTimeout(300);
      await expect(drawer).toHaveAttribute("aria-hidden", "true");
    });

    test(`mobile scroll contract stays document-owned on ${path}`, async ({
      page,
    }) => {
      const viewport = { width: 390, height: 844 };

      await openRoute(page, path, viewport);

      const burgerButton = page.locator("#mobile-menu-button");
      const closeButton = page.locator("#mobile-menu-close");

      await expectDocumentOwnedScroll(page, viewport);

      await burgerButton.click();
      await expect(burgerButton).toHaveAttribute("aria-expanded", "true");
      await expectDocumentOwnedScroll(page, viewport);

      await closeButton.click();
      await page.waitForTimeout(300);
      await expect(burgerButton).toHaveAttribute("aria-expanded", "false");
      await expectDocumentOwnedScroll(page, viewport);
    });

    test(`desktop more dropdown opens and closes on ${path}`, async ({
      page,
    }) => {
      const viewport = { width: 1280, height: 900 };

      await openRoute(page, path, viewport);

      const burgerButton = page.locator("#mobile-menu-button");
      const desktopNav = page.locator(".main-nav.header-nav");
      const moreTrigger = page.locator(".more-dropdown-trigger");
      const moreMenu = page.locator(".more-dropdown-menu");
      const faqLink = page.locator(".more-dropdown-menu a[href='faq.php']");

      await expect(burgerButton).toBeHidden();
      await expect(desktopNav).toBeVisible();
      await expect(moreTrigger).toBeVisible();
      await expect(moreTrigger).toHaveAttribute("aria-expanded", "false");

      await moreTrigger.click();
      await expect(moreTrigger).toHaveAttribute("aria-expanded", "true");
      await expect(moreMenu).toBeVisible();
      await expect(faqLink).toBeVisible();

      await page.keyboard.press("Escape");
      await page.waitForTimeout(200);
      await expect(moreTrigger).toHaveAttribute("aria-expanded", "false");

      await moreTrigger.click();
      await expect(moreMenu).toBeVisible();
      await clickDesktopOutsideMenu(page);
      await page.waitForTimeout(200);
      await expect(moreTrigger).toHaveAttribute("aria-expanded", "false");
    });

    test(`desktop scroll contract stays document-owned on ${path}`, async ({
      page,
    }) => {
      const viewport = { width: 1280, height: 900 };

      await openRoute(page, path, viewport);

      const moreTrigger = page.locator(".more-dropdown-trigger");

      await expectDocumentOwnedScroll(page, viewport);

      await moreTrigger.click();
      await expect(moreTrigger).toHaveAttribute("aria-expanded", "true");
      await expectDocumentOwnedScroll(page, viewport);

      await page.keyboard.press("Escape");
      await page.waitForTimeout(200);
      await expect(moreTrigger).toHaveAttribute("aria-expanded", "false");
      await expectDocumentOwnedScroll(page, viewport);
    });

    test(`narrow desktop uses burger contract on ${path}`, async ({ page }) => {
      const viewport = { width: 1024, height: 768 };

      await openRoute(page, path, viewport);

      const burgerButton = page.locator("#mobile-menu-button");
      const desktopNav = page.locator(".main-nav.header-nav");
      const drawer = page.locator("#mobile-menu");

      const contract = await readScrollContract(page);

      expectClosedAssistantContract(contract);
      const probe = await wheelProbe(page, viewport);
      expect(Math.max(probe.deltaWindow, probe.deltaHtml)).toBeGreaterThan(0);
      expect(Math.abs(probe.deltaBody)).toBeLessThan(1);
      expect(Math.abs(probe.deltaMain)).toBeLessThan(1);

      await expect(burgerButton).toBeVisible();
      await expect(desktopNav).toBeHidden();

      await burgerButton.click();
      await expect(burgerButton).toHaveAttribute("aria-expanded", "true");
      await expect(drawer).toHaveAttribute("aria-hidden", "false");
      await expect(drawer).toBeVisible();
    });
  }
});
