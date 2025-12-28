/**
 * Production Scroll Debug Test
 * Tests scroll on https://blackbox.codes directly
 */
const { test, expect } = require("@playwright/test");

const PRODUCTION_URL = "https://blackbox.codes";

test.describe("Production Scroll Debug", () => {
  test("desktop scroll should work on production", async ({ page }) => {
    // Set desktop viewport
    await page.setViewportSize({ width: 1440, height: 900 });

    // Go to production
    await page.goto(PRODUCTION_URL, { waitUntil: "networkidle" });

    // Wait for page to fully load
    await page.waitForTimeout(2000);

    // Check current scroll position
    const initialScroll = await page.evaluate(() => window.scrollY);
    console.log(`Initial scroll position: ${initialScroll}`);

    // Check body classes
    const bodyClasses = await page.evaluate(() => document.body.className);
    console.log(`Body classes: ${bodyClasses}`);

    // Check html classes
    const htmlClasses = await page.evaluate(
      () => document.documentElement.className
    );
    console.log(`HTML classes: ${htmlClasses}`);

    // Check computed styles on body
    const bodyStyles = await page.evaluate(() => {
      const body = document.body;
      const styles = window.getComputedStyle(body);
      return {
        overflow: styles.overflow,
        overflowY: styles.overflowY,
        overflowX: styles.overflowX,
        position: styles.position,
        height: styles.height,
        touchAction: styles.touchAction,
        pointerEvents: styles.pointerEvents,
        visibility: styles.visibility,
        opacity: styles.opacity,
      };
    });
    console.log("Body computed styles:", JSON.stringify(bodyStyles, null, 2));

    // Check computed styles on html
    const htmlStyles = await page.evaluate(() => {
      const html = document.documentElement;
      const styles = window.getComputedStyle(html);
      return {
        overflow: styles.overflow,
        overflowY: styles.overflowY,
        overflowX: styles.overflowX,
        position: styles.position,
        height: styles.height,
        touchAction: styles.touchAction,
        pointerEvents: styles.pointerEvents,
        visibility: styles.visibility,
        opacity: styles.opacity,
      };
    });
    console.log("HTML computed styles:", JSON.stringify(htmlStyles, null, 2));

    // Check document height
    const docHeight = await page.evaluate(
      () => document.documentElement.scrollHeight
    );
    console.log(`Document scroll height: ${docHeight}`);

    // Try to scroll using wheel event
    await page.mouse.wheel(0, 500);
    await page.waitForTimeout(500);

    const afterWheelScroll = await page.evaluate(() => window.scrollY);
    console.log(`After wheel scroll: ${afterWheelScroll}`);

    // Try JavaScript scroll
    await page.evaluate(() => window.scrollTo(0, 300));
    await page.waitForTimeout(500);

    const afterJsScroll = await page.evaluate(() => window.scrollY);
    console.log(`After JS scroll: ${afterJsScroll}`);

    // Check if any overlay is blocking
    const overlays = await page.evaluate(() => {
      const elements = document.querySelectorAll("*");
      const overlayElements = [];
      for (const el of elements) {
        const style = window.getComputedStyle(el);
        const rect = el.getBoundingClientRect();
        // Check for fullscreen fixed/absolute elements that might block scroll
        if (
          (style.position === "fixed" || style.position === "absolute") &&
          rect.width >= window.innerWidth * 0.9 &&
          rect.height >= window.innerHeight * 0.9 &&
          style.pointerEvents !== "none" &&
          style.visibility !== "hidden" &&
          style.display !== "none" &&
          parseFloat(style.opacity) > 0
        ) {
          overlayElements.push({
            tag: el.tagName,
            id: el.id,
            classes: el.className,
            position: style.position,
            zIndex: style.zIndex,
            pointerEvents: style.pointerEvents,
            width: rect.width,
            height: rect.height,
          });
        }
      }
      return overlayElements;
    });

    if (overlays.length > 0) {
      console.log("POTENTIAL BLOCKING OVERLAYS FOUND:");
      overlays.forEach((o) => console.log(JSON.stringify(o, null, 2)));
    } else {
      console.log("No blocking overlays detected");
    }

    // Final conclusion - check if JS scroll worked
    console.log(
      `CONCLUSION: JS scroll to 300 returned position: ${afterJsScroll}`
    );
    console.log(
      `CONCLUSION: Mouse wheel returned position: ${afterWheelScroll}`
    );

    // The test passes if EITHER scroll method worked
    const scrollWorks = afterJsScroll > 0 || afterWheelScroll > 0;
    console.log(`SCROLL WORKS: ${scrollWorks}`);

    expect(scrollWorks).toBe(true);
  });

  test("check for scroll-blocking CSS rules", async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto(PRODUCTION_URL, { waitUntil: "networkidle" });
    await page.waitForTimeout(2000);

    // Check all stylesheets for overflow:hidden on body/html
    const blockingRules = await page.evaluate(() => {
      const rules = [];
      for (const sheet of document.styleSheets) {
        try {
          for (const rule of sheet.cssRules || []) {
            const ruleText = rule.cssText;
            if (
              (ruleText.includes("body") || ruleText.includes("html")) &&
              (ruleText.includes("overflow: hidden") ||
                ruleText.includes("overflow:hidden") ||
                ruleText.includes("visibility: hidden") ||
                ruleText.includes("visibility:hidden"))
            ) {
              rules.push({
                selector: rule.selectorText || "unknown",
                text: ruleText.substring(0, 500),
                href: sheet.href || "inline",
              });
            }
          }
        } catch (e) {
          // Cross-origin stylesheet, skip
        }
      }
      return rules;
    });

    console.log("Potentially blocking CSS rules:");
    if (blockingRules.length === 0) {
      console.log("  None found");
    } else {
      blockingRules.forEach((r) => console.log(`  ${r.href}: ${r.selector}`));
    }
  });

  test("check event listeners that might prevent scroll", async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto(PRODUCTION_URL, { waitUntil: "networkidle" });
    await page.waitForTimeout(2000);

    // Check if wheel event is being prevented
    const wheelBlocked = await page.evaluate(() => {
      let blocked = false;
      const handler = (e) => {
        if (e.defaultPrevented) {
          blocked = true;
        }
      };
      window.addEventListener("wheel", handler, {
        passive: false,
        capture: true,
      });

      // Dispatch a wheel event
      const event = new WheelEvent("wheel", {
        deltaY: 100,
        bubbles: true,
        cancelable: true,
      });
      document.body.dispatchEvent(event);

      window.removeEventListener("wheel", handler, { capture: true });
      return blocked;
    });

    console.log(`Wheel event blocked: ${wheelBlocked}`);

    // Check for touchmove prevention (iOS)
    const touchBlocked = await page.evaluate(() => {
      let blocked = false;
      const handler = (e) => {
        if (e.defaultPrevented) {
          blocked = true;
        }
      };
      document.addEventListener("touchmove", handler, {
        passive: false,
        capture: true,
      });

      const event = new TouchEvent("touchmove", {
        bubbles: true,
        cancelable: true,
        touches: [
          new Touch({
            identifier: 0,
            target: document.body,
            clientX: 100,
            clientY: 100,
          }),
        ],
      });
      document.body.dispatchEvent(event);

      document.removeEventListener("touchmove", handler, { capture: true });
      return blocked;
    });

    console.log(`Touch event blocked: ${touchBlocked}`);
  });
});
