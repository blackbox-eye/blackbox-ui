const { test, expect } = require("@playwright/test");

const ROUTES = [
  "/",
  "/about.php",
  "/products.php",
  "/pricing.php",
  "/demo.php",
  "/faq.php",
  "/free-scan.php",
  "/contact.php",
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

const MOBILE_DRAWER_VIEWPORTS = VIEWPORTS.filter(
  (viewport) => viewport.width <= 834,
);
const DESKTOP_HEADER_VIEWPORTS = VIEWPORTS.filter(
  (viewport) => viewport.width >= 1280,
);

async function openRoute(page, route, viewport) {
  await page.setViewportSize({
    width: viewport.width,
    height: viewport.height,
  });
  const response = await page.goto(route, { waitUntil: "domcontentloaded" });

  expect(response?.status() ?? 0).toBeGreaterThan(0);
  expect(response?.status() ?? 500).toBeLessThan(500);

  await page.waitForLoadState("networkidle", { timeout: 5000 }).catch(() => {});
}

async function expectNoHorizontalOverflow(page, route, viewport) {
  const overflowState = await page.evaluate(() => ({
    doc: document.documentElement.scrollWidth,
    body: document.body.scrollWidth,
    viewport: window.innerWidth,
  }));

  expect(
    overflowState.doc,
    `document overflow on ${route} @ ${viewport.label}`,
  ).toBeLessThanOrEqual(overflowState.viewport + 1);
  expect(
    overflowState.body,
    `body overflow on ${route} @ ${viewport.label}`,
  ).toBeLessThanOrEqual(overflowState.viewport + 1);
}

async function expectHeroContentStable(page, route, viewport) {
  const heroTitle = page
    .locator(
      '.graphene-hero-title, .products-hero__title, .access-hero__title, h1[class*="hero"]',
    )
    .first();

  if ((await heroTitle.count()) === 0) {
    return;
  }

  await expect(heroTitle).toBeVisible();

  const titleState = await heroTitle.evaluate((element) => ({
    clientWidth: element.clientWidth,
    scrollWidth: element.scrollWidth,
    lineHeight: parseFloat(getComputedStyle(element).lineHeight),
    height: element.getBoundingClientRect().height,
  }));

  expect(
    titleState.scrollWidth,
    `hero title overflow on ${route} @ ${viewport.label}`,
  ).toBeLessThanOrEqual(titleState.clientWidth + 1);
  expect(
    titleState.height,
    `hero title collapsed on ${route} @ ${viewport.label}`,
  ).toBeGreaterThanOrEqual(Math.max(titleState.lineHeight - 1, 1));
}

async function expectBreadcrumbReadable(page, route, viewport) {
  const currentCrumb = page
    .locator(
      '.breadcrumb .breadcrumb-item[aria-current="page"] span[itemprop="name"]',
    )
    .first();

  if ((await currentCrumb.count()) === 0) {
    return;
  }

  await expect(currentCrumb).toBeVisible();

  const crumbState = await currentCrumb.evaluate((element) => {
    const rect = element.getBoundingClientRect();
    const headerRect = document
      .getElementById("main-header")
      ?.getBoundingClientRect();
    return {
      left: rect.left,
      top: rect.top,
      right: rect.right,
      bottom: rect.bottom,
      scrollWidth: element.scrollWidth,
      clientWidth: element.clientWidth,
      headerBottom: headerRect?.bottom ?? 0,
    };
  });

  expect(
    crumbState.left,
    `breadcrumb clipped left on ${route} @ ${viewport.label}`,
  ).toBeGreaterThanOrEqual(0);
  expect(
    crumbState.right,
    `breadcrumb clipped right on ${route} @ ${viewport.label}`,
  ).toBeLessThanOrEqual(viewport.width - 8);
  expect(
    crumbState.scrollWidth,
    `breadcrumb text overflow on ${route} @ ${viewport.label}`,
  ).toBeLessThanOrEqual(crumbState.clientWidth + 2);
  expect(
    crumbState.top,
    `breadcrumb overlaps header on ${route} @ ${viewport.label}`,
  ).toBeGreaterThanOrEqual(crumbState.headerBottom - 1);
}

async function openDrawer(page) {
  const burgerButton = page.locator("#mobile-menu-button");
  await expect(burgerButton).toBeVisible();
  await burgerButton.click();
  await expect(page.locator("#mobile-menu")).toHaveAttribute(
    "aria-hidden",
    "false",
  );
}

async function expectDrawerFooterStable(page) {
  const footerState = await page.locator("#mobile-menu > div:last-child").evaluate((element) => {
    const styles = getComputedStyle(element);
    return {
      backgroundColor: styles.backgroundColor,
      borderTopColor: styles.borderTopColor,
    };
  });

  expect(footerState.backgroundColor).not.toBe("rgba(0, 0, 0, 0)");
  expect(footerState.borderTopColor).not.toBe("rgba(0, 0, 0, 0)");
}

test.describe("Public mobile responsive UX stability", () => {
  for (const viewport of VIEWPORTS) {
    test(`target routes keep hero, breadcrumb, and page shell stable @ ${viewport.label}`, async ({
      page,
    }) => {
      for (const route of ROUTES) {
        await openRoute(page, route, viewport);
        await expectNoHorizontalOverflow(page, route, viewport);
        await expectHeroContentStable(page, route, viewport);
        await expectBreadcrumbReadable(page, route, viewport);
      }
    });
  }

  for (const viewport of MOBILE_DRAWER_VIEWPORTS) {
    test(`home hero CTA stack stays clickable on compact/mobile widths @ ${viewport.label}`, async ({
      page,
    }) => {
      await openRoute(page, "/", viewport);

      const ctas = page.locator(
        ".graphene-cta-group a, .graphene-cta-group button",
      );
      const count = await ctas.count();
      expect(count).toBeGreaterThan(0);

      for (let index = 0; index < Math.min(count, 2); index += 1) {
        const cta = ctas.nth(index);
        await expect(cta).toBeVisible();
        await expect(cta).toBeEnabled();

        const hitTargetState = await cta.evaluate((element) => {
          const rect = element.getBoundingClientRect();
          return {
            width: rect.width,
            height: rect.height,
            right: rect.right,
          };
        });

        expect(hitTargetState.height).toBeGreaterThanOrEqual(48);
        expect(hitTargetState.right).toBeLessThanOrEqual(viewport.width - 8);
      }
    });
  }

  for (const viewport of MOBILE_DRAWER_VIEWPORTS.slice(0, 2)) {
    test(`drawer footer language and theme state update cleanly @ ${viewport.label}`, async ({
      page,
    }) => {
      await openRoute(page, "/about.php", viewport);

      await openDrawer(page);

      const daSwitch = page.locator("#mobile-menu a[data-lang-target='da']");
      const enSwitch = page.locator("#mobile-menu a[data-lang-target='en']");
      const themeToggle = page.locator(
        "#mobile-menu button[data-theme-toggle]",
      );
      const aboutLink = page.locator("#mobile-menu a[href='about.php']");

      await expect(daSwitch).toBeVisible();
      await expect(enSwitch).toBeVisible();
      await expect(themeToggle).toBeVisible();
      await expect(aboutLink).toContainText("About");
      await expect
        .poll(async () =>
          themeToggle.evaluate((button) => button.dataset.themeTextCurrent || ""),
        )
        .not.toBe("");
      await expectDrawerFooterStable(page);

      const currentTheme = await page.evaluate(
        () => document.documentElement.dataset.theme || "dark",
      );
      await themeToggle.click();
      await expect
        .poll(async () =>
          page.evaluate(() => document.documentElement.dataset.theme),
        )
        .not.toBe(currentTheme);

      await Promise.all([
        page.waitForLoadState("domcontentloaded"),
        daSwitch.click(),
      ]);
      await page
        .waitForLoadState("networkidle", { timeout: 5000 })
        .catch(() => {});

      await expect
        .poll(async () => page.evaluate(() => window.location.pathname))
        .toBe("/about.php");

      await openDrawer(page);
      await expect(
        page.locator("#mobile-menu a[data-lang-target='da']"),
      ).toHaveAttribute("aria-current", "true");
      await expect(
        page.locator("#mobile-menu a[data-lang-target='en']"),
      ).not.toHaveAttribute("aria-current", "true");
      await expect(page.locator("#mobile-menu a[href='about.php']")).toContainText(
        "Om Os",
      );
      await expect
        .poll(async () => page.evaluate(() => document.body.dataset.lang))
        .toBe("da");
    });
  }

  test("offline assistant is explicit on /about.php @ 390x844", async ({ page }) => {
    await openRoute(page, "/about.php", VIEWPORTS[0]);

    await page.locator("#alphabot-toggle-btn").click();

    await expect(page.locator("#alphabot-container")).toHaveAttribute(
      "data-assistant-state",
      "offline",
    );
    await expect(page.locator("#alphabot-input")).toBeDisabled();
    await expect(page.locator("#alphabot-send-btn")).toBeDisabled();
    await expect(page.locator("#alphabot-input")).toHaveAttribute(
      "placeholder",
      "",
    );
    await expect(page.locator("#alphabot-hint")).toContainText(/offline|Kontakt support/i);
  });

  for (const viewport of DESKTOP_HEADER_VIEWPORTS) {
    test(`desktop theme toggle keeps visible label text on /about.php @ ${viewport.label}`, async ({
      page,
    }) => {
      await openRoute(page, "/about.php", viewport);

      const themeToggle = page.locator(
        ".header-actions .theme-toggle:not(.theme-toggle--mobile)",
      );
      await expect(themeToggle).toBeVisible();
      await expect
        .poll(async () =>
          themeToggle.evaluate((button) => ({
            text: button.dataset.themeTextCurrent || "",
            width: button.getBoundingClientRect().width,
          })),
        )
        .toEqual(
          expect.objectContaining({
            text: expect.stringMatching(/Dark|Light|Mørkt|Lyst/),
            width: expect.any(Number),
          }),
        );
    });
  }

  test("pricing AI advisor button stays emoji-free on /pricing.php @ 390x844", async ({
    page,
  }) => {
    await openRoute(page, "/pricing.php", VIEWPORTS[0]);

    const aiButton = page.locator("button").filter({ hasText: /AI/i }).first();
    await expect(aiButton).toBeVisible();
    await expect(aiButton).not.toContainText("✨");
  });
});
