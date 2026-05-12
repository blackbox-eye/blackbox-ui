const { test, expect } = require("@playwright/test");

const HASHES = ["#ccs", "#gdi", "#intel24", ""];
const VIEWPORTS = [
  { width: 390, height: 844 },
  { width: 430, height: 932 },
  { width: 768, height: 1024 },
  { width: 834, height: 1112 },
  { width: 1024, height: 768 },
  { width: 1280, height: 900 },
  { width: 1440, height: 900 },
];

async function getLayoutSnapshot(page) {
  return page.evaluate(() => {
    const grid = document.querySelector(".console-selector__grid");
    const activity = document.querySelector(".console-selector__activity");
    const cards = Array.from(document.querySelectorAll(".console-card")).map(
      (card) => {
        const rect = card.getBoundingClientRect();
        const cta = card.querySelector(".console-card__cta");
        const ctaRect = cta ? cta.getBoundingClientRect() : null;
        const disabled = cta
          ? cta.getAttribute("aria-disabled") === "true"
          : false;

        return {
          console: card.getAttribute("data-console"),
          left: rect.left,
          right: rect.right,
          top: rect.top,
          bottom: rect.bottom,
          width: rect.width,
          ctaVisible: !!ctaRect,
          ctaDisabled: disabled,
          ctaLeft: ctaRect ? ctaRect.left : null,
          ctaRight: ctaRect ? ctaRect.right : null,
          ctaTop: ctaRect ? ctaRect.top : null,
          ctaBottom: ctaRect ? ctaRect.bottom : null,
        };
      },
    );

    const gridRect = grid.getBoundingClientRect();
    const activityRect = activity.getBoundingClientRect();
    const style = getComputedStyle(grid);

    return {
      viewport: {
        width: window.innerWidth,
        height: window.innerHeight,
      },
      document: {
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
      },
      grid: {
        display: style.display,
        gridTemplateColumns: style.gridTemplateColumns,
        gap: style.gap,
        left: gridRect.left,
        right: gridRect.right,
        top: gridRect.top,
        bottom: gridRect.bottom,
      },
      activity: {
        left: activityRect.left,
        right: activityRect.right,
        top: activityRect.top,
        bottom: activityRect.bottom,
      },
      cards,
    };
  });
}

async function isCtaClickable(page, consoleName) {
  const cta = page.locator(
    `[data-console="${consoleName}"] .console-card__cta`,
  );
  await cta.scrollIntoViewIfNeeded();

  try {
    await cta.click({ trial: true });
    return true;
  } catch {
    return false;
  }
}

async function gotoAgentAccess(page, route) {
  await page.addInitScript(() => {
    localStorage.removeItem("bbx_console_favorites");
  });
  await page.goto(route);
  await page.waitForSelector(".console-selector__grid");
  await waitForScrollToSettle(page);
}

async function waitForScrollToSettle(page) {
  await page.waitForFunction(
    () => {
      const currentY = window.scrollY;
      const now = performance.now();
      const previous = window.__bbxScrollStableState;

      if (!previous || Math.abs(previous.y - currentY) > 1) {
        window.__bbxScrollStableState = { y: currentY, since: now };
        return false;
      }

      return now - previous.since >= 250;
    },
    { timeout: 3000 },
  );
}

async function revealTriggerBelowHeader(page, trigger) {
  await trigger.scrollIntoViewIfNeeded();

  const [triggerBox, headerBox] = await Promise.all([
    trigger.boundingBox(),
    page
      .locator("#main-header")
      .boundingBox()
      .catch(() => null),
  ]);

  if (!triggerBox || !headerBox) {
    return;
  }

  const minimumTriggerTop = headerBox.y + headerBox.height + 16;
  if (triggerBox.y < minimumTriggerTop) {
    await page.mouse.wheel(0, triggerBox.y - minimumTriggerTop);
    await page.waitForTimeout(150);
  }

  await waitForScrollToSettle(page);
}

async function openConsoleSlideout(page, consoleName) {
  const trigger = page.locator(
    `[data-console="${consoleName}"] .console-card__info-btn`,
  );
  const panelSelector = `#${consoleName}-slideout`;
  let lastError;

  await revealTriggerBelowHeader(page, trigger);

  for (let attempt = 0; attempt < 2; attempt += 1) {
    try {
      await trigger.click({ timeout: 1500 });
    } catch (error) {
      lastError = error;
      await page.waitForTimeout(250);
      await revealTriggerBelowHeader(page, trigger);
      continue;
    }

    try {
      await page.waitForFunction(
        (selector) =>
          document.querySelector(selector)?.classList.contains("is-open"),
        panelSelector,
        { timeout: 1500 },
      );
      await page.waitForTimeout(250);
      return page.locator(panelSelector);
    } catch (error) {
      lastError = error;
      // Retry once if the first click lands during the route's own deep-link sync.
      await page.waitForTimeout(250);
      await revealTriggerBelowHeader(page, trigger);
    }
  }

  throw lastError || new Error(`Slideout did not open for ${consoleName}`);
}

async function getMobileSheetMetrics(page, consoleName) {
  return page.evaluate((currentConsole) => {
    const panel = document.getElementById(`${currentConsole}-slideout`);
    const panelBody = panel?.querySelector(".console-card__slideout-body");
    const viewportWidth = window.visualViewport?.width ?? window.innerWidth;
    const docEl = document.documentElement;
    const body = document.body;

    function getRect(element) {
      if (!element) {
        return null;
      }

      const rect = element.getBoundingClientRect();
      return {
        left: rect.left,
        right: rect.right,
        top: rect.top,
        bottom: rect.bottom,
        width: rect.width,
        height: rect.height,
      };
    }

    const trackedSelectors = [
      ".console-card__slideout-header",
      ".console-card__slideout-body",
      ".console-card__opsbar",
      ".console-card__opschip",
      ".console-card__readiness",
      ".console-card__readiness-row",
      ".console-card__metrics-grid",
      ".console-card__metric",
      ".console-card__metric-value",
      ".console-card__metric-label",
    ];

    const overflowingChildren = trackedSelectors.flatMap((selector) =>
      Array.from(panel?.querySelectorAll(selector) ?? []).flatMap((element) => {
        const style = getComputedStyle(element);
        if (style.display === "none" || style.visibility === "hidden") {
          return [];
        }

        const rect = element.getBoundingClientRect();
        const overflowsViewport =
          rect.left < -1 || rect.right > viewportWidth + 1;
        const overflowsSelf =
          element.clientWidth > 0 &&
          element.scrollWidth > element.clientWidth + 1;

        if (!overflowsViewport && !overflowsSelf) {
          return [];
        }

        return [
          {
            selector,
            left: rect.left,
            right: rect.right,
            width: rect.width,
            scrollWidth: element.scrollWidth,
            clientWidth: element.clientWidth,
          },
        ];
      }),
    );

    let hitCoverage = null;
    if (panelBody) {
      const rect = panelBody.getBoundingClientRect();
      const pointX = rect.left + rect.width / 2;
      const pointY = rect.top + Math.min(48, rect.height / 2);
      const hit = document.elementFromPoint(pointX, pointY);
      hitCoverage = {
        insideSlideout: !!hit && !!hit.closest(`#${currentConsole}-slideout`),
        coveringCard:
          !!hit &&
          !!hit.closest(".console-card") &&
          !hit.closest(`#${currentConsole}-slideout`),
        coveringActivity:
          !!hit &&
          !!hit.closest(".console-selector__activity") &&
          !hit.closest(`#${currentConsole}-slideout`),
      };
    }

    return {
      viewportWidth,
      position: panel ? getComputedStyle(panel).position : null,
      panel: getRect(panel),
      panelBody: panelBody
        ? {
            ...getRect(panelBody),
            scrollWidth: panelBody.scrollWidth,
            clientWidth: panelBody.clientWidth,
          }
        : null,
      document: {
        scrollWidth: docEl.scrollWidth,
        clientWidth: docEl.clientWidth,
      },
      body: {
        scrollWidth: body.scrollWidth,
        clientWidth: body.clientWidth,
      },
      overflowingChildren,
      hitCoverage,
    };
  }, consoleName);
}

function boxesOverlap(firstBox, secondBox) {
  if (!firstBox || !secondBox) {
    return false;
  }

  return !(
    firstBox.x + firstBox.width <= secondBox.x ||
    secondBox.x + secondBox.width <= firstBox.x ||
    firstBox.y + firstBox.height <= secondBox.y ||
    secondBox.y + secondBox.height <= firstBox.y
  );
}

function countDistinctColumns(cards, tolerance = 12) {
  const columns = [];

  for (const card of cards) {
    const existingColumn = columns.find(
      (left) => Math.abs(left - card.left) <= tolerance,
    );

    if (existingColumn === undefined) {
      columns.push(card.left);
    }
  }

  return columns.length;
}

for (const viewport of VIEWPORTS) {
  test.describe(`agent-access layout ${viewport.width}x${viewport.height}`, () => {
    test.use({ viewport });

    for (const hash of HASHES) {
      const route = `/agent-access.php${hash}`;

      test(`keeps the console layout contract on ${route}`, async ({
        page,
      }) => {
        await gotoAgentAccess(page, route);

        const cards = page.locator(".console-card");
        await expect(cards).toHaveCount(3);

        const snapshot = await getLayoutSnapshot(page);

        expect(snapshot.document.scrollWidth).toBeLessThanOrEqual(
          snapshot.document.clientWidth + 1,
        );
        expect(snapshot.cards).toHaveLength(3);

        for (const card of snapshot.cards) {
          expect(
            card.left,
            `${card.console} card should not be off-canvas left`,
          ).toBeGreaterThanOrEqual(-1);
          expect(
            card.right,
            `${card.console} card should not be off-canvas right`,
          ).toBeLessThanOrEqual(snapshot.viewport.width + 1);
          expect(card.ctaVisible, `${card.console} CTA should be visible`).toBe(
            true,
          );
          expect(
            card.ctaLeft,
            `${card.console} CTA should stay inside viewport left edge`,
          ).toBeGreaterThanOrEqual(-1);
          expect(
            card.ctaRight,
            `${card.console} CTA should stay inside viewport right edge`,
          ).toBeLessThanOrEqual(snapshot.viewport.width + 1);

          if (!card.ctaDisabled) {
            const ctaClickable = await isCtaClickable(page, card.console);
            expect(
              ctaClickable,
              `${card.console} CTA should be clickable once scrolled into view`,
            ).toBe(true);
          }
        }

        const lowestCardBottom = Math.max(
          ...snapshot.cards.map((card) => card.bottom),
        );
        expect(
          snapshot.activity.top,
          "Recent activity should render below the card grid",
        ).toBeGreaterThanOrEqual(lowestCardBottom - 1);

        const minCardWidth = Math.min(
          ...snapshot.cards.map((card) => card.width),
        );

        if (snapshot.viewport.width >= 834 && snapshot.viewport.width <= 1024) {
          expect(
            countDistinctColumns(snapshot.cards),
            "Agent Access should collapse to a readable tablet stack at 834-1024px",
          ).toBe(1);
          expect(
            minCardWidth,
            "Tablet card width should stay comfortably readable",
          ).toBeGreaterThan(640);
        }

        if (snapshot.viewport.width >= 1280) {
          expect(
            snapshot.grid.display,
            "Desktop grid should remain a shared grid layout at >=1280px",
          ).not.toBe("flex");
          expect(
            minCardWidth,
            "Desktop cards should keep a readable multi-column width",
          ).toBeGreaterThan(340);
        }
      });
    }
  });
}

test.describe("agent-access quick switch state", () => {
  test.use({ viewport: { width: 1280, height: 900 } });

  test("pinning a different console does not change the active quick switch selection", async ({
    page,
  }) => {
    await gotoAgentAccess(page, "/agent-access.php#ccs");

    const quickSwitch = page.locator("[data-console-quick-switch]");
    await expect(quickSwitch).toHaveValue("ccs");

    await page.locator('[data-console="gdi"] .console-card__fav-btn').click();

    await expect(page).toHaveURL(/#ccs$/);
    await expect(quickSwitch).toHaveValue("ccs");
  });
});

for (const viewport of [
  { width: 390, height: 844 },
  { width: 430, height: 932 },
  { width: 768, height: 1024 },
]) {
  test.describe(`agent-access mobile sheet ${viewport.width}x${viewport.height}`, () => {
    test.use({ viewport });

    test("mobile slideout renders as an isolated readable bottom sheet and closes cleanly", async ({
      page,
    }) => {
      await gotoAgentAccess(page, "/agent-access.php#ccs");

      for (const consoleName of ["ccs", "gdi", "intel24"]) {
        const slideout = await openConsoleSlideout(page, consoleName);
        await expect(slideout).toBeVisible();
        await expect(slideout).toHaveAttribute("aria-hidden", "false");

        const closeButton = slideout.locator(".console-card__slideout-close");
        const metrics = await getMobileSheetMetrics(page, consoleName);

        expect(
          metrics.position,
          `${consoleName} mobile detail panel should stay in fixed sheet mode`,
        ).toBe("fixed");
        expect(metrics.panel).toBeTruthy();
        expect(metrics.panelBody).toBeTruthy();
        expect(metrics.panel.left).toBeGreaterThanOrEqual(-1);
        expect(metrics.panel.right).toBeLessThanOrEqual(
          metrics.viewportWidth + 1,
        );
        expect(metrics.panelBody.left).toBeGreaterThanOrEqual(-1);
        expect(metrics.panelBody.right).toBeLessThanOrEqual(
          metrics.viewportWidth + 1,
        );
        expect(
          metrics.panel.width,
          `${consoleName} mobile sheet should use the visible viewport width`,
        ).toBeGreaterThan(viewport.width - 32);
        expect(
          metrics.panelBody.height,
          `${consoleName} mobile sheet body should remain readable`,
        ).toBeGreaterThan(80);
        expect(metrics.panelBody.scrollWidth).toBeLessThanOrEqual(
          metrics.panelBody.clientWidth + 1,
        );
        expect(metrics.document.scrollWidth).toBeLessThanOrEqual(
          metrics.document.clientWidth + 1,
        );
        expect(metrics.body.scrollWidth).toBeLessThanOrEqual(
          metrics.body.clientWidth + 1,
        );
        expect(
          metrics.overflowingChildren,
          `${consoleName} mobile sheet should keep header/body/status/metrics inside the viewport`,
        ).toHaveLength(0);
        expect(metrics.hitCoverage.insideSlideout).toBe(true);
        expect(metrics.hitCoverage.coveringCard).toBe(false);
        expect(metrics.hitCoverage.coveringActivity).toBe(false);

        await expect(closeButton).toBeVisible();
        await closeButton.click();
        await expect(slideout).toHaveAttribute("aria-hidden", "true");
      }
    });
  });
}

for (const viewport of [
  { width: 834, height: 1112 },
  { width: 1024, height: 768 },
  { width: 1280, height: 900 },
  { width: 1440, height: 900 },
]) {
  test.describe(`agent-access slideout fit ${viewport.width}x${viewport.height}`, () => {
    test.use({ viewport });

    for (const consoleName of ["ccs", "gdi", "intel24"]) {
      test(`${consoleName} info panel stays in console flow without covering other content`, async ({
        page,
      }) => {
        await gotoAgentAccess(page, "/agent-access.php#" + consoleName);

        const panel = await openConsoleSlideout(page, consoleName);
        await expect(panel).toBeVisible();

        const quickSwitch = page.locator(".console-selector__quick");
        const activity = page.locator(".console-selector__activity");
        const card = page.locator(`[data-console="${consoleName}"]`);
        const panelBody = panel.locator(".console-card__slideout-body");
        const closeButton = panel.locator(".console-card__slideout-close");

        const [box, bodyBox, heroBox, quickSwitchBox, activityBox, cardBox] =
          await Promise.all([
            panel.boundingBox(),
            panelBody.boundingBox(),
            page.locator(".access-hero").boundingBox(),
            quickSwitch.boundingBox(),
            activity.boundingBox(),
            card.boundingBox(),
          ]);

        const footerBox = await page
          .locator("footer")
          .first()
          .boundingBox()
          .catch(() => null);

        expect(box).toBeTruthy();
        expect(bodyBox).toBeTruthy();
        expect(heroBox).toBeTruthy();
        expect(quickSwitchBox).toBeTruthy();
        expect(activityBox).toBeTruthy();
        expect(cardBox).toBeTruthy();
        expect(box.x).toBeGreaterThanOrEqual(-1);
        expect(box.x + box.width).toBeLessThanOrEqual(viewport.width + 1);
        if (viewport.width <= 1024) {
          expect(
            cardBox.width,
            `${consoleName} tablet card should stay wide enough to avoid cramped copy`,
          ).toBeGreaterThan(640);
          expect(
            box.width,
            `${consoleName} tablet info panel should retain near-full card width`,
          ).toBeGreaterThan(cardBox.width - 96);
        }
        expect(boxesOverlap(box, heroBox)).toBe(false);
        expect(boxesOverlap(box, quickSwitchBox)).toBe(false);
        expect(boxesOverlap(box, activityBox)).toBe(false);
        if (footerBox) {
          expect(boxesOverlap(box, footerBox)).toBe(false);
        }
        expect(box.y).toBeGreaterThanOrEqual(cardBox.y - 1);
        expect(bodyBox.height).toBeGreaterThan(120);

        const documentWidth = await page.evaluate(() => ({
          scrollWidth: document.documentElement.scrollWidth,
          clientWidth: document.documentElement.clientWidth,
        }));

        expect(documentWidth.scrollWidth).toBeLessThanOrEqual(
          documentWidth.clientWidth + 1,
        );

        await expect(closeButton).toBeVisible();
        await closeButton.click();
        await expect(panel).toHaveAttribute("aria-hidden", "true");
      });
    }
  });
}
