<?php
/**
 * P0 iOS Scroll Debug Kill-Switch
 * 
 * Query parameters to isolate scroll-blocking elements:
 *   ?nosurface=1  - Disable ALL: sticky CTA, chat widget
 *   ?nocta=1      - Disable sticky CTA / Priority Access bar only
 *   ?nochat=1     - Disable chat widget (Alphabot) only
 * 
 * Usage: https://blackbox.codes/index.php?nosurface=1
 * 
 * DOM elements disabled:
 *   - Sticky CTA:    #sticky-cta (z-index: 75, position: fixed, bottom: 0)
 *   - Sticky CTA Bar:#sticky-cta-bar (non-landing pages)
 *   - Chat Widget:   #alphabot-container, #alphabot-panel, #alphabot-overlay
 * 
 * NOTE: Cookie banner has been completely removed from the codebase (P0 iOS scroll fix).
 */

// Parse kill-switch query parameters
$_BBX_KILLSWITCH = [
    'nosurface' => isset($_GET['nosurface']) && $_GET['nosurface'] == '1',
    'nocta'     => isset($_GET['nocta']) && $_GET['nocta'] == '1',
    'nochat'    => isset($_GET['nochat']) && $_GET['nochat'] == '1',
];

// Convenience flags (cookie banner removed - flag kept for backward compatibility but always false)
$_BBX_DISABLE_COOKIE = false;
$_BBX_DISABLE_CTA    = $_BBX_KILLSWITCH['nosurface'] || $_BBX_KILLSWITCH['nocta'];
$_BBX_DISABLE_CHAT   = $_BBX_KILLSWITCH['nosurface'] || $_BBX_KILLSWITCH['nochat'];

// Output debug comment in HTML if any kill-switch is active
function bbx_killswitch_debug_comment(): string {
    global $_BBX_KILLSWITCH, $_BBX_DISABLE_CTA, $_BBX_DISABLE_CHAT;
    
    if (!$_BBX_DISABLE_CTA && !$_BBX_DISABLE_CHAT) {
        return '';
    }
    
    $disabled = [];
    if ($_BBX_DISABLE_CTA) $disabled[] = 'sticky-cta';
    if ($_BBX_DISABLE_CHAT) $disabled[] = 'alphabot-chat';
    
    return sprintf(
        "\n<!-- P0 DEBUG: Kill-switch active. Disabled elements: %s -->\n",
        implode(', ', $disabled)
    );
}

// Output inline CSS to force-hide elements (belt + suspenders approach)
function bbx_killswitch_inline_css(): string {
    global $_BBX_DISABLE_CTA, $_BBX_DISABLE_CHAT;
    
    if (!$_BBX_DISABLE_CTA && !$_BBX_DISABLE_CHAT) {
        return '';
    }
    
    $css = '<style id="p0-killswitch-css">';
    
    if ($_BBX_DISABLE_CTA) {
        $css .= '
/* P0 KILLSWITCH: Sticky CTA disabled */
#sticky-cta,
#sticky-cta-bar,
.sticky-cta-bar,
[data-component="sticky-cta"] {
    display: none !important;
    visibility: hidden !important;
    pointer-events: none !important;
    opacity: 0 !important;
}';
    }
    
    if ($_BBX_DISABLE_CHAT) {
        $css .= '
/* P0 KILLSWITCH: Chat widget (Alphabot) disabled */
#alphabot-container,
#alphabot-panel,
#alphabot-overlay,
#alphabot-toggle-btn,
.alphabot-widget,
.alphabot-toggle,
.alphabot-overlay,
.bbx-command-rail {
    display: none !important;
    visibility: hidden !important;
    pointer-events: none !important;
    opacity: 0 !important;
}';
    }
    
    $css .= '</style>';
    return $css;
}
