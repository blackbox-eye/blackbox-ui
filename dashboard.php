<?php

/**
 * Dashboard - Blackbox EYE Command Center
 *
 * Modern dashboard interface with responsive card-based layout.
 * Uses admin-layout.php for consistent navigation via Control Panel.
 * Displays alerts, system status, network monitoring, and AI interface.
 */

session_start();
if (empty($_SESSION['agent_id'])) {
    header('Location: gdi-login.php');
    exit;
}

// Set page variables for admin layout
$page_title = 'Dashboard';
$current_admin_page = 'dashboard';

// Include admin layout header
include __DIR__ . '/includes/admin-layout.php';
?>

<!-- Dashboard Page Styles -->
<style>
    /* Dashboard-specific variables */
    .dashboard {
        --dash-critical: #F85149;
        --dash-warning: var(--color-primary, #c9a227);
        --dash-success: #4ade80;
        --dash-info: #60a5fa;
    }

    /* Dashboard Grid Layout */
    .dashboard__grid {
        display: grid;
        gap: var(--admin-spacing-md);
        grid-template-columns: repeat(12, 1fr);
        grid-template-rows: auto;
    }

    /* Card base styles */
    .dashboard__card {
        background: var(--admin-bg-secondary);
        border: 1px solid var(--admin-border-subtle);
        border-radius: var(--admin-border-radius);
        padding: var(--admin-spacing-lg);
        transition: border-color 0.25s ease, transform 0.25s ease;
    }

    .dashboard__card:hover {
        border-color: var(--admin-border-gold);
    }

    .dashboard__card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--admin-spacing-md);
        padding-bottom: var(--admin-spacing-sm);
        border-bottom: 1px solid var(--admin-border-subtle);
    }

    .dashboard__card-title {
        display: flex;
        align-items: center;
        gap: var(--admin-spacing-sm);
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--admin-text-gold);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin: 0;
    }

    .dashboard__card-title svg {
        width: 18px;
        height: 18px;
        color: var(--admin-gold);
    }

    .dashboard__card-badge {
        font-size: 0.6rem;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .dashboard__card-badge--critical {
        background: rgba(248, 81, 73, 0.15);
        color: var(--dash-critical);
        border: 1px solid rgba(248, 81, 73, 0.3);
    }

    .dashboard__card-badge--warning {
        background: rgba(251, 191, 36, 0.15);
        color: var(--dash-warning);
        border: 1px solid rgba(251, 191, 36, 0.3);
    }

    .dashboard__card-badge--success {
        background: rgba(74, 222, 128, 0.15);
        color: var(--dash-success);
        border: 1px solid rgba(74, 222, 128, 0.3);
    }

    /* Grid placement for different screen sizes */
    .dashboard__card--alerts {
        grid-column: span 12;
    }

    .dashboard__card--status {
        grid-column: span 12;
    }

    .dashboard__card--network {
        grid-column: span 12;
    }

    .dashboard__card--ai {
        grid-column: span 12;
    }

    .dashboard__card--chart {
        grid-column: span 12;
    }

    @media (min-width: 768px) {
        .dashboard__card--alerts {
            grid-column: span 6;
        }

        .dashboard__card--status {
            grid-column: span 6;
        }

        .dashboard__card--network {
            grid-column: span 6;
        }

        .dashboard__card--ai {
            grid-column: span 6;
        }

        .dashboard__card--chart {
            grid-column: span 12;
        }
    }

    @media (min-width: 1024px) {
        .dashboard__card--alerts {
            grid-column: span 4;
        }

        .dashboard__card--status {
            grid-column: span 4;
        }

        .dashboard__card--network {
            grid-column: span 4;
        }

        .dashboard__card--ai {
            grid-column: span 6;
        }

        .dashboard__card--chart {
            grid-column: span 6;
        }
    }

    /* Alert item styles */
    .dashboard__alert {
        padding: var(--admin-spacing-sm) var(--admin-spacing-md);
        margin-bottom: var(--admin-spacing-sm);
        background: rgba(0, 0, 0, 0.2);
        border-radius: var(--admin-border-radius-sm);
        border-left: 3px solid var(--dash-warning);
    }

    .dashboard__alert--critical {
        border-left-color: var(--dash-critical);
        animation: pulse-alert 2s infinite;
    }

    @keyframes pulse-alert {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(248, 81, 73, 0.2);
        }

        50% {
            box-shadow: 0 0 8px 4px rgba(248, 81, 73, 0);
        }
    }

    .dashboard__alert-title {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--admin-text-primary);
        margin: 0 0 0.25rem;
    }

    .dashboard__alert-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.65rem;
        color: var(--admin-text-muted);
    }

    .dashboard__alert-action {
        color: var(--admin-gold);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .dashboard__alert-action:hover {
        color: var(--admin-gold-light);
    }

    /* Status list styles */
    .dashboard__status-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .dashboard__status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--admin-spacing-sm) 0;
        border-bottom: 1px solid var(--admin-border-subtle);
        font-size: 0.78rem;
    }

    .dashboard__status-item:last-child {
        border-bottom: none;
    }

    .dashboard__status-label {
        color: var(--admin-text-secondary);
    }

    .dashboard__status-badge {
        font-size: 0.6rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .dashboard__status-badge--ok {
        background: rgba(74, 222, 128, 0.15);
        color: var(--dash-success);
    }

    .dashboard__status-badge--warning {
        background: rgba(251, 191, 36, 0.15);
        color: var(--dash-warning);
    }

    .dashboard__status-badge--critical {
        background: rgba(248, 81, 73, 0.15);
        color: var(--dash-critical);
    }

    /* Network progress bars */
    .dashboard__network-item {
        margin-bottom: var(--admin-spacing-md);
    }

    .dashboard__network-item:last-child {
        margin-bottom: 0;
    }

    .dashboard__network-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.72rem;
        margin-bottom: 0.35rem;
    }

    .dashboard__network-label {
        color: var(--admin-text-secondary);
    }

    .dashboard__network-value {
        color: var(--admin-text-primary);
        font-weight: 500;
    }

    .dashboard__network-bar {
        height: 6px;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 3px;
        overflow: hidden;
    }

    .dashboard__network-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    .dashboard__network-fill--low {
        background: var(--dash-info);
    }

    .dashboard__network-fill--medium {
        background: var(--dash-warning);
    }

    .dashboard__network-fill--high {
        background: var(--dash-critical);
    }

    /* AI Command Interface */
    .dashboard__ai-input {
        width: 100%;
        min-height: 120px;
        padding: var(--admin-spacing-md);
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid var(--admin-border-subtle);
        border-radius: var(--admin-border-radius);
        color: var(--admin-text-primary);
        font-family: 'Fira Code', monospace;
        font-size: 0.78rem;
        resize: vertical;
        transition: border-color 0.2s;
    }

    .dashboard__ai-input::placeholder {
        color: var(--admin-text-muted);
    }

    .dashboard__ai-input:focus {
        outline: none;
        border-color: var(--admin-gold);
    }

    .dashboard__ai-hint {
        font-size: 0.65rem;
        color: var(--admin-text-muted);
        margin-top: var(--admin-spacing-sm);
    }

    /* Chart container */
    .dashboard__chart-container {
        position: relative;
        height: 220px;
    }

    /* Stats row */
    .dashboard__stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--admin-spacing-md);
        margin-bottom: var(--admin-spacing-lg);
    }

    @media (min-width: 640px) {
        .dashboard__stats {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .dashboard__stat {
        background: var(--admin-bg-secondary);
        border: 1px solid var(--admin-border-subtle);
        border-radius: var(--admin-border-radius);
        padding: var(--admin-spacing-md);
        text-align: center;
        transition: border-color 0.2s, transform 0.2s;
    }

    .dashboard__stat:hover {
        border-color: var(--admin-border-gold);
        transform: translateY(-2px);
    }

    .dashboard__stat-value {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--admin-text-gold);
        line-height: 1;
        transition: color 0.3s;
    }

    .dashboard__stat-value.updating {
        opacity: 0.5;
    }

    .dashboard__stat-label {
        font-size: 0.68rem;
        color: var(--admin-text-muted);
        margin-top: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Threat Overview Hero Card */
    .dashboard__threat-hero {
        grid-column: span 12;
        background: linear-gradient(135deg, rgba(248, 81, 73, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
        border: 1px solid rgba(248, 81, 73, 0.3);
        border-radius: var(--admin-border-radius);
        padding: var(--admin-spacing-xl);
        margin-bottom: var(--admin-spacing-lg);
        display: grid;
        grid-template-columns: 1fr;
        gap: var(--admin-spacing-lg);
    }

    @media (min-width: 768px) {
        .dashboard__threat-hero {
            grid-template-columns: 1fr 2fr;
        }
    }

    .dashboard__threat-score {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .dashboard__threat-score-value {
        font-size: 4rem;
        font-weight: 700;
        line-height: 1;
        background: linear-gradient(135deg, var(--dash-warning), var(--dash-critical));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .dashboard__threat-score-label {
        font-size: 0.75rem;
        color: var(--admin-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-top: 0.5rem;
    }

    .dashboard__threat-score-status {
        display: inline-block;
        margin-top: 1rem;
        padding: 0.35rem 1rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .dashboard__threat-score-status--elevated {
        background: rgba(251, 191, 36, 0.2);
        color: var(--dash-warning);
        border: 1px solid rgba(251, 191, 36, 0.4);
    }

    .dashboard__threat-score-status--critical {
        background: rgba(248, 81, 73, 0.2);
        color: var(--dash-critical);
        border: 1px solid rgba(248, 81, 73, 0.4);
        animation: pulse-status 2s infinite;
    }

    .dashboard__threat-score-status--low {
        background: rgba(74, 222, 128, 0.2);
        color: var(--dash-success);
        border: 1px solid rgba(74, 222, 128, 0.4);
    }

    @keyframes pulse-status {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .dashboard__threat-details {
        display: flex;
        flex-direction: column;
        gap: var(--admin-spacing-md);
    }

    .dashboard__threat-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--admin-spacing-sm) var(--admin-spacing-md);
        background: rgba(0, 0, 0, 0.2);
        border-radius: var(--admin-border-radius-sm);
    }

    .dashboard__threat-detail-label {
        font-size: 0.78rem;
        color: var(--admin-text-secondary);
    }

    .dashboard__threat-detail-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--admin-text-gold);
    }

    .dashboard__threat-detail-value--critical {
        color: var(--dash-critical);
    }

    .dashboard__threat-detail-value--warning {
        color: var(--dash-warning);
    }

    /* AI Command Log */
    .dashboard__ai-log {
        max-height: 150px;
        overflow-y: auto;
        margin-top: var(--admin-spacing-md);
        border-top: 1px solid var(--admin-border-subtle);
        padding-top: var(--admin-spacing-md);
    }

    .dashboard__ai-log-item {
        display: flex;
        align-items: flex-start;
        gap: var(--admin-spacing-sm);
        padding: var(--admin-spacing-xs) 0;
        font-size: 0.72rem;
        border-bottom: 1px solid var(--admin-border-subtle);
    }

    .dashboard__ai-log-item:last-child {
        border-bottom: none;
    }

    .dashboard__ai-log-time {
        color: var(--admin-text-muted);
        white-space: nowrap;
        min-width: 70px;
    }

    .dashboard__ai-log-command {
        color: var(--admin-gold);
        font-family: 'Fira Code', monospace;
    }

    .dashboard__ai-log-status {
        margin-left: auto;
        padding: 0.1rem 0.4rem;
        border-radius: 4px;
        font-size: 0.6rem;
        text-transform: uppercase;
    }

    .dashboard__ai-log-status--completed {
        background: rgba(74, 222, 128, 0.15);
        color: var(--dash-success);
    }

    .dashboard__ai-log-status--pending {
        background: rgba(251, 191, 36, 0.15);
        color: var(--dash-warning);
    }

    /* Loading spinner */
    .dashboard__loading {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid var(--admin-border-subtle);
        border-top-color: var(--admin-gold);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Last updated indicator */
    .dashboard__last-updated {
        font-size: 0.65rem;
        color: var(--admin-text-muted);
        text-align: right;
        margin-top: var(--admin-spacing-sm);
    }

    /* Global loading indicator for polling */
    .dashboard__polling-indicator {
        position: fixed;
        top: var(--admin-spacing-md);
        right: var(--admin-spacing-md);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--admin-bg-secondary);
        border: 1px solid var(--admin-border-gold);
        border-radius: var(--admin-border-radius);
        font-size: 0.7rem;
        color: var(--admin-text-gold);
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.3s ease, transform 0.3s ease;
        z-index: 100;
        pointer-events: none;
    }

    .dashboard__polling-indicator.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* Screen reader only - for live regions */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* Mobile: Collapsible secondary sections */
    .dashboard__section-toggle {
        display: none;
        width: 100%;
        padding: var(--admin-spacing-sm) var(--admin-spacing-md);
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid var(--admin-border-subtle);
        border-radius: var(--admin-border-radius);
        color: var(--admin-text-secondary);
        font-size: 0.75rem;
        cursor: pointer;
        margin-bottom: var(--admin-spacing-sm);
        transition: background 0.2s, border-color 0.2s;
    }

    .dashboard__section-toggle:hover,
    .dashboard__section-toggle:focus {
        background: rgba(0, 0, 0, 0.3);
        border-color: var(--admin-border-gold);
        outline: none;
    }

    .dashboard__section-toggle svg {
        width: 12px;
        height: 12px;
        margin-left: 0.5rem;
        transition: transform 0.2s;
    }

    .dashboard__section-toggle[aria-expanded="true"] svg {
        transform: rotate(180deg);
    }

    @media (max-width: 767px) {
        .dashboard__section-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dashboard__card--secondary {
            display: none;
        }

        .dashboard__card--secondary.is-expanded {
            display: block;
        }
    }
</style>

<!-- Polling Indicator -->
<div class="dashboard__polling-indicator" id="pollingIndicator" aria-hidden="true">
    <span class="dashboard__loading"></span>
    <span>Opdaterer data...</span>
</div>

<!-- ARIA Live Region for screen readers -->
<div id="dashboardLiveRegion" class="sr-only" aria-live="polite" aria-atomic="false"></div>

<!-- Dashboard Content -->
<div class="dashboard admin-page">
    <!-- Page Header -->
    <header class="admin-page__header">
        <div>
            <h1 class="admin-page__title">
                <span class="admin-page__title-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>
                </span>
                Dashboard
            </h1>
            <p class="admin-page__subtitle">
                Velkommen, <strong><?= htmlspecialchars($_SESSION['agent_id']) ?></strong> — Systemstatus oversigt
            </p>
        </div>
        <div>
            <span class="dashboard__card-badge dashboard__card-badge--success">
                System Online
            </span>
        </div>
    </header>

    <!-- Stats Row -->
    <div class="dashboard__stats">
        <div class="dashboard__stat">
            <div class="dashboard__stat-value" id="statAlerts">—</div>
            <div class="dashboard__stat-label">Aktive Alarmer</div>
        </div>
        <div class="dashboard__stat">
            <div class="dashboard__stat-value" id="statThreats">—</div>
            <div class="dashboard__stat-label">Trusler i dag</div>
        </div>
        <div class="dashboard__stat">
            <div class="dashboard__stat-value" id="statUptime">—</div>
            <div class="dashboard__stat-label">System Uptime</div>
        </div>
        <div class="dashboard__stat">
            <div class="dashboard__stat-value" id="statRequests">—</div>
            <div class="dashboard__stat-label">API Requests</div>
        </div>
    </div>

    <!-- Threat Overview Hero Card -->
    <div class="dashboard__threat-hero" id="threatHero">
        <div class="dashboard__threat-score">
            <div class="dashboard__threat-score-value" id="threatScore">—</div>
            <div class="dashboard__threat-score-label">Trusselsniveau</div>
            <span class="dashboard__threat-score-status dashboard__threat-score-status--elevated" id="threatStatus">
                Indlæser...
            </span>
        </div>
        <div class="dashboard__threat-details">
            <div class="dashboard__threat-detail">
                <span class="dashboard__threat-detail-label">Kritiske hændelser (aktive)</span>
                <span class="dashboard__threat-detail-value dashboard__threat-detail-value--critical" id="criticalCount">—</span>
            </div>
            <div class="dashboard__threat-detail">
                <span class="dashboard__threat-detail-label">Advarsler under observation</span>
                <span class="dashboard__threat-detail-value dashboard__threat-detail-value--warning" id="warningCount">—</span>
            </div>
            <div class="dashboard__threat-detail">
                <span class="dashboard__threat-detail-label">Blokerede angreb i dag</span>
                <span class="dashboard__threat-detail-value" id="blockedCount">—</span>
            </div>
            <div class="dashboard__threat-detail">
                <span class="dashboard__threat-detail-label">Seneste trussel opdaget</span>
                <span class="dashboard__threat-detail-value" id="lastThreatTime" style="font-size: 0.85rem;">—</span>
            </div>
        </div>
    </div>

    <!-- Mobile Section Toggles -->
    <button type="button"
        class="dashboard__section-toggle"
        id="toggleSecondaryCards"
        aria-expanded="false"
        aria-controls="secondaryCardsSection">
        <span>Vis netværk & AI interface</span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9" />
        </svg>
    </button>

    <!-- Card Grid -->
    <div class="dashboard__grid">

        <!-- Active Alerts Card -->
        <div class="dashboard__card dashboard__card--alerts">
            <header class="dashboard__card-header">
                <h2 class="dashboard__card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    Aktive Alarmer
                </h2>
                <span class="dashboard__card-badge dashboard__card-badge--critical" id="alertsBadge">
                    <span class="dashboard__loading"></span>
                </span>
            </header>
            <div id="alertsContainer">
                <div style="text-align: center; padding: 2rem; color: var(--admin-text-muted);">
                    <span class="dashboard__loading"></span>
                    <p style="margin-top: 0.5rem; font-size: 0.75rem;">Indlæser alarmer...</p>
                </div>
            </div>
        </div>

        <!-- System Status Card -->
        <div class="dashboard__card dashboard__card--status">
            <header class="dashboard__card-header">
                <h2 class="dashboard__card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                    Systemstatus
                </h2>
                <span class="dashboard__card-badge dashboard__card-badge--success" id="systemHealthBadge">
                    <span class="dashboard__loading"></span>
                </span>
            </header>
            <ul class="dashboard__status-list" id="systemStatusList">
                <li style="text-align: center; padding: 2rem; color: var(--admin-text-muted);">
                    <span class="dashboard__loading"></span>
                    <p style="margin-top: 0.5rem; font-size: 0.75rem;">Tjekker services...</p>
                </li>
            </ul>
        </div>

        <!-- Network Monitoring Card -->
        <div class="dashboard__card dashboard__card--network dashboard__card--secondary" id="secondaryCardsSection">
            <header class="dashboard__card-header">
                <h2 class="dashboard__card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12.55a11 11 0 0 1 14.08 0" />
                        <path d="M1.42 9a16 16 0 0 1 21.16 0" />
                        <path d="M8.53 16.11a6 6 0 0 1 6.95 0" />
                        <line x1="12" y1="20" x2="12.01" y2="20" />
                    </svg>
                    Netværksovervågning
                </h2>
            </header>
            <div id="networkContainer">
                <div style="text-align: center; padding: 2rem; color: var(--admin-text-muted);">
                    <span class="dashboard__loading"></span>
                    <p style="margin-top: 0.5rem; font-size: 0.75rem;">Henter netværksdata...</p>
                </div>
            </div>
            <div class="dashboard__last-updated" id="networkLastUpdated"></div>
        </div>

        <!-- Server Load Chart Card -->
        <div class="dashboard__card dashboard__card--chart">
            <header class="dashboard__card-header">
                <h2 class="dashboard__card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10" />
                        <line x1="12" y1="20" x2="12" y2="4" />
                        <line x1="6" y1="20" x2="6" y2="14" />
                    </svg>
                    Serverbelastning
                </h2>
            </header>
            <div class="dashboard__chart-container">
                <canvas id="serverLoadChart" role="img" aria-label="Graf over serverbelastning"></canvas>
            </div>
        </div>

        <!-- AI Command Interface Card -->
        <div class="dashboard__card dashboard__card--ai dashboard__card--secondary">
            <header class="dashboard__card-header">
                <h2 class="dashboard__card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="10" rx="2" />
                        <circle cx="12" cy="5" r="2" />
                        <path d="M12 7v4" />
                        <line x1="8" y1="16" x2="8" y2="16" />
                        <line x1="16" y1="16" x2="16" y2="16" />
                    </svg>
                    AI Kommando Interface
                </h2>
            </header>
            <p style="font-size: 0.75rem; color: var(--admin-text-secondary); margin-bottom: var(--admin-spacing-md);">
                Stil et spørgsmål eller giv en kommando til GREY-E AI assistenten.
            </p>
            <form id="aiCommandForm">
                <label for="aiCommandInput" class="sr-only">Indtast AI kommando</label>
                <textarea
                    id="aiCommandInput"
                    class="dashboard__ai-input"
                    placeholder="> Analysér trafik fra IP 192.168.1.100..."
                    aria-describedby="aiCommandHint"
                    rows="3"></textarea>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: var(--admin-spacing-sm);">
                    <p class="dashboard__ai-hint" id="aiCommandHint">
                        Tryk <kbd>Ctrl+Enter</kbd> for at sende kommando
                    </p>
                    <button type="submit" id="aiSubmitBtn" class="dashboard__card-badge dashboard__card-badge--success" style="cursor: pointer; border: none; padding: 0.4rem 1rem;" aria-label="Send kommando til AI">
                        <span class="ai-submit-text">Send Kommando</span>
                        <span class="ai-submit-spinner dashboard__loading" style="display: none;" aria-hidden="true"></span>
                    </button>
                </div>
            </form>

            <!-- AI Response Area -->
            <div id="aiResponseArea" style="display: none; margin-top: var(--admin-spacing-md); padding: var(--admin-spacing-md); background: rgba(0,0,0,0.3); border-radius: var(--admin-border-radius-sm); border-left: 3px solid var(--admin-gold);">
                <div style="font-size: 0.7rem; color: var(--admin-text-muted); margin-bottom: 0.5rem;">GREY-E Response:</div>
                <div id="aiResponseText" style="font-size: 0.8rem; color: var(--admin-text-primary); line-height: 1.5;"></div>
            </div>

            <!-- Command Log -->
            <div class="dashboard__ai-log" id="aiCommandLog">
                <div style="text-align: center; padding: 1rem; color: var(--admin-text-muted); font-size: 0.72rem;">
                    Indlæser kommandohistorik...
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"
    integrity="sha256-s4B2di9zY7yekStouOA0gmeY213ya7YfAA7C56MTe8c="
    crossorigin="anonymous"></script>

<!-- Dashboard JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // ===== Dashboard API Integration =====
        const API_BASE = 'api/';
        const REFRESH_INTERVAL = 30000; // 30 seconds
        const AI_TIMEOUT = 15000; // 15 seconds for AI commands

        let serverLoadChart = null;
        const pollingIndicator = document.getElementById('pollingIndicator');
        const liveRegion = document.getElementById('dashboardLiveRegion');

        // ===== ARIA Live Region Announcer =====
        function announceToScreenReader(message) {
            if (liveRegion) {
                liveRegion.textContent = message;
                // Clear after announcement
                setTimeout(() => {
                    liveRegion.textContent = '';
                }, 1000);
            }
        }

        // ===== Polling Indicator =====
        function showPollingIndicator() {
            if (pollingIndicator) {
                pollingIndicator.classList.add('is-visible');
                pollingIndicator.setAttribute('aria-hidden', 'false');
            }
        }

        function hidePollingIndicator() {
            if (pollingIndicator) {
                pollingIndicator.classList.remove('is-visible');
                pollingIndicator.setAttribute('aria-hidden', 'true');
            }
        }

        // ===== Mobile Section Toggle =====
        const toggleBtn = document.getElementById('toggleSecondaryCards');
        const secondaryCards = document.querySelectorAll('.dashboard__card--secondary');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                toggleBtn.setAttribute('aria-expanded', !isExpanded);
                toggleBtn.querySelector('span').textContent = isExpanded ? 'Vis netværk & AI interface' : 'Skjul netværk & AI interface';
                secondaryCards.forEach(card => {
                    card.classList.toggle('is-expanded', !isExpanded);
                });
            });
        }

        // Format time ago helper
        function timeAgo(timestamp) {
            if (!timestamp) return 'Ukendt';

            const now = new Date();
            const date = new Date(timestamp);

            // Check for invalid date
            if (isNaN(date.getTime())) return 'Ukendt';

            const diff = Math.floor((now - date) / 1000);

            if (diff < 0) return 'Lige nu';
            if (diff < 60) return `${diff} sek. siden`;
            if (diff < 3600) return `${Math.floor(diff / 60)} min. siden`;
            if (diff < 86400) return `${Math.floor(diff / 3600)} timer siden`;
            return `${Math.floor(diff / 86400)} dage siden`;
        }

        // Fetch with error handling
        async function apiFetch(endpoint, showIndicator = false) {
            try {
                if (showIndicator) showPollingIndicator();
                const response = await fetch(API_BASE + endpoint, {
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error(`API Error (${endpoint}):`, error);
                return null;
            } finally {
                if (showIndicator) hidePollingIndicator();
            }
        }

        // ===== Load Dashboard Stats =====
        async function loadDashboardStats(isRefresh = false) {
            const data = await apiFetch('dashboard-stats.php', isRefresh);
            if (data && data.success) {
                const stats = data.data;
                document.getElementById('statAlerts').textContent = stats.alerts_count ?? '0';
                document.getElementById('statThreats').textContent = stats.threats_today ?? '0';
                document.getElementById('statUptime').textContent = `${stats.uptime_percent ?? 99.9}%`;
                document.getElementById('statRequests').textContent = (stats.api_requests ?? 0).toLocaleString('da-DK');

                // Update threat hero
                updateThreatHero(stats);

                // Announce update to screen readers
                if (isRefresh) {
                    announceToScreenReader(`Dashboard opdateret. ${stats.alerts_count ?? 0} aktive alarmer, ${stats.threats_today ?? 0} trusler i dag.`);
                }
            }
        }

        // ===== Update Threat Hero Card =====
        function updateThreatHero(stats) {
            const criticalCount = stats.critical_count ?? 0;
            const warningCount = stats.warning_count ?? 0;
            const blockedCount = stats.blocked_count ?? 0;
            const lastThreat = stats.last_threat_time ?? null;

            // Calculate threat score (0-100)
            let score = Math.min(100, criticalCount * 25 + warningCount * 5);
            let statusClass = 'low';
            let statusText = 'Normalt';

            if (score >= 75) {
                statusClass = 'critical';
                statusText = 'Kritisk';
            } else if (score >= 25) {
                statusClass = 'elevated';
                statusText = 'Forhøjet';
            }

            document.getElementById('threatScore').textContent = score;
            document.getElementById('criticalCount').textContent = criticalCount;
            document.getElementById('warningCount').textContent = warningCount;
            document.getElementById('blockedCount').textContent = blockedCount;
            document.getElementById('lastThreatTime').textContent = lastThreat ? timeAgo(lastThreat) : 'Ingen nylige';

            const statusEl = document.getElementById('threatStatus');
            statusEl.className = `dashboard__threat-score-status dashboard__threat-score-status--${statusClass}`;
            statusEl.textContent = statusText;
        }

        // ===== Load Alerts =====
        async function loadAlerts() {
            const data = await apiFetch('alerts.php?limit=5');
            const container = document.getElementById('alertsContainer');
            const badge = document.getElementById('alertsBadge');

            if (!container) return;

            if (data && data.success && data.data.length > 0) {
                const alerts = data.data;
                badge.innerHTML = `${alerts.length} aktive`;

                container.innerHTML = alerts.map(alert => `
                    <div class="dashboard__alert ${alert.severity === 'critical' ? 'dashboard__alert--critical' : ''}">
                        <h3 class="dashboard__alert-title">${escapeHtml(alert.title)}</h3>
                        <div class="dashboard__alert-meta">
                            <span>
                                <span class="dashboard__card-badge dashboard__card-badge--${alert.severity === 'critical' ? 'critical' : 'warning'}" style="margin-right: 0.5rem; font-size: 0.6rem;">
                                    ${alert.severity.toUpperCase()}
                                </span>
                                ${alert.time_ago || timeAgo(alert.created_at)} • ${escapeHtml(alert.target || 'System')}
                            </span>
                            <a href="#" class="dashboard__alert-action" data-alert-id="${alert.id}">Undersøg →</a>
                        </div>
                    </div>
                `).join('');
            } else {
                badge.innerHTML = '0';
                badge.className = 'dashboard__card-badge dashboard__card-badge--success';
                container.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: var(--admin-text-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; opacity: 0.3; margin-bottom: 1rem;">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <p style="font-size: 0.8rem;">Ingen aktive alarmer</p>
                        <p style="font-size: 0.7rem; margin-top: 0.5rem;">Alle systemer kører normalt</p>
                    </div>
                `;
            }
        }

        // ===== Load System Status =====
        async function loadSystemStatus() {
            const data = await apiFetch('system-status.php');
            const container = document.getElementById('systemStatusList');
            const badge = document.getElementById('systemHealthBadge');

            if (!container) return;

            if (data && data.success && data.data.services) {
                const services = data.data.services;
                const allOperational = services.every(s => s.status === 'operational');
                const hasWarnings = services.some(s => s.status === 'warning');
                const hasDegraded = services.some(s => s.status === 'degraded' || s.status === 'offline');

                if (allOperational) {
                    badge.innerHTML = 'Alle OK';
                    badge.className = 'dashboard__card-badge dashboard__card-badge--success';
                } else if (hasDegraded) {
                    badge.innerHTML = 'Problemer';
                    badge.className = 'dashboard__card-badge dashboard__card-badge--critical';
                } else if (hasWarnings) {
                    badge.innerHTML = 'Advarsler';
                    badge.className = 'dashboard__card-badge dashboard__card-badge--warning';
                }

                container.innerHTML = services.map(service => {
                    const statusClass = {
                        'operational': 'ok',
                        'warning': 'warning',
                        'degraded': 'warning',
                        'offline': 'error'
                    } [service.status] || 'ok';

                    const statusText = {
                        'operational': 'Operationel',
                        'warning': 'Advarsel',
                        'degraded': 'Degraderet',
                        'offline': 'Offline'
                    } [service.status] || service.status;

                    return `
                        <li class="dashboard__status-item dashboard__status-item--${statusClass}">
                            <span class="dashboard__status-indicator"></span>
                            <span class="dashboard__status-name">${escapeHtml(service.name)}</span>
                            <span class="dashboard__status-info">${service.latency_ms}ms</span>
                        </li>
                    `;
                }).join('');
            } else {
                badge.innerHTML = 'Fejl';
                badge.className = 'dashboard__card-badge dashboard__card-badge--critical';
                container.innerHTML = `
                    <li style="text-align: center; padding: 1rem; color: var(--admin-text-muted);">
                        Kunne ikke hente systemstatus
                    </li>
                `;
            }
        }

        // ===== Load Network Stats =====
        async function loadNetworkStats() {
            const data = await apiFetch('network-stats.php');
            const container = document.getElementById('networkContainer');
            const lastUpdated = document.getElementById('networkLastUpdated');

            if (!container) return;

            if (data && data.success && data.data.ports) {
                const ports = data.data.ports;

                container.innerHTML = `
                    <div class="dashboard__network-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--admin-spacing-sm);">
                        ${ports.map(port => {
                            const color = port.level === 'high' ? 'var(--dash-critical)' : port.level === 'medium' ? 'var(--dash-warning)' : 'var(--dash-info)';
                            return `
                            <div class="dashboard__network-port" style="background: rgba(0,0,0,0.2); padding: var(--admin-spacing-sm); border-radius: var(--admin-border-radius-sm); border-left: 3px solid ${color};">
                                <div style="font-size: 0.7rem; color: var(--admin-text-muted);">Port ${port.port}</div>
                                <div style="font-size: 1.1rem; font-weight: 600; color: ${color};">${port.utilization}%</div>
                                <div style="font-size: 0.65rem; color: var(--admin-text-secondary);">${escapeHtml(port.name)}</div>
                            </div>
                        `}).join('')}
                    </div>
                `;

                if (lastUpdated) {
                    lastUpdated.textContent = `Sidst opdateret: ${new Date().toLocaleTimeString('da-DK')}`;
                }
            } else {
                container.innerHTML = `
                    <div style="text-align: center; padding: 1rem; color: var(--admin-text-muted);">
                        Kunne ikke hente netværksdata
                    </div>
                `;
            }
        }

        // ===== Load AI Command History =====
        async function loadAICommandHistory() {
            const data = await apiFetch('ai-command.php?limit=5');
            const container = document.getElementById('aiCommandLog');

            if (!container) return;

            if (data && data.success && data.data.length > 0) {
                const commands = data.data;

                container.innerHTML = commands.map(cmd => `
                    <div class="dashboard__ai-log-item">
                        <span class="dashboard__ai-log-time">${timeAgo(cmd.timestamp)}</span>
                        <span class="dashboard__ai-log-command">> ${escapeHtml(cmd.command.substring(0, 40))}${cmd.command.length > 40 ? '...' : ''}</span>
                        <span class="dashboard__ai-log-status dashboard__ai-log-status--${cmd.status === 'completed' ? 'completed' : 'pending'}">
                            ${cmd.status === 'completed' ? 'Fuldført' : 'Afventer'}
                        </span>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div style="text-align: center; padding: 1rem; color: var(--admin-text-muted); font-size: 0.72rem;">
                        Ingen tidligere kommandoer
                    </div>
                `;
            }
        }

        // ===== Submit AI Command =====
        async function submitAICommand(command) {
            const responseArea = document.getElementById('aiResponseArea');
            const responseText = document.getElementById('aiResponseText');
            const submitBtn = document.getElementById('aiSubmitBtn');
            const submitText = submitBtn?.querySelector('.ai-submit-text');
            const submitSpinner = submitBtn?.querySelector('.ai-submit-spinner');

            // Show spinner
            if (submitBtn) {
                submitBtn.disabled = true;
                if (submitText) submitText.style.display = 'none';
                if (submitSpinner) submitSpinner.style.display = 'inline-block';
            }

            responseArea.style.display = 'block';
            responseText.innerHTML = '<span class="dashboard__loading"></span> Behandler kommando...';
            responseText.setAttribute('aria-busy', 'true');

            // Create AbortController for timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), AI_TIMEOUT);

            try {
                const response = await fetch(API_BASE + 'ai-command.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    signal: controller.signal,
                    body: JSON.stringify({
                        command
                    })
                });

                clearTimeout(timeoutId);
                const data = await response.json();

                if (data.success) {
                    responseText.textContent = data.data.response || 'Kommando modtaget og behandles.';
                    announceToScreenReader('AI kommando udført succesfuldt');
                    loadAICommandHistory(); // Refresh history
                } else {
                    responseText.innerHTML = `<span style="color: var(--dash-critical);" role="alert">Fejl: ${escapeHtml(data.error || 'Ukendt fejl')}</span>`;
                    announceToScreenReader('AI kommando fejlede');
                }
            } catch (error) {
                clearTimeout(timeoutId);
                if (error.name === 'AbortError') {
                    responseText.innerHTML = `<span style="color: var(--dash-critical);" role="alert">Timeout: Kommandoen tog for lang tid (max ${AI_TIMEOUT / 1000} sekunder). Prøv igen.</span>`;
                    announceToScreenReader('AI kommando timeout');
                } else {
                    responseText.innerHTML = `<span style="color: var(--dash-critical);" role="alert">Netværksfejl: ${escapeHtml(error.message)}</span>`;
                    announceToScreenReader('AI kommando netværksfejl');
                }
            } finally {
                responseText.setAttribute('aria-busy', 'false');
                // Reset button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    if (submitText) submitText.style.display = 'inline';
                    if (submitSpinner) submitSpinner.style.display = 'none';
                }
            }
        }

        // ===== Setup AI Command Form =====
        function setupAICommandForm() {
            const form = document.getElementById('aiCommandForm');
            const input = document.getElementById('aiCommandInput');

            if (!form || !input) return;

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const command = input.value.trim();
                if (command) {
                    submitAICommand(command);
                    input.value = '';
                }
            });

            // Ctrl+Enter to submit
            input.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });
        }

        // ===== Server Load Chart =====
        function initServerLoadChart() {
            const ctx = document.getElementById('serverLoadChart');
            if (!ctx) return;

            serverLoadChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({
                        length: 12
                    }, (_, i) => `${60 - i * 5}m`),
                    datasets: [{
                        label: 'CPU Belastning',
                        data: Array(12).fill(0),
                        borderColor: 'rgba(212, 175, 55, 1)',
                        backgroundColor: 'rgba(212, 175, 55, 0.15)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0
                    }, {
                        label: 'Hukommelsesbrug',
                        data: Array(12).fill(0),
                        borderColor: 'rgba(96, 165, 250, 1)',
                        backgroundColor: 'rgba(96, 165, 250, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                color: 'rgba(255,255,255,0.5)',
                                callback: (v) => v + '%'
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.08)'
                            }
                        },
                        x: {
                            ticks: {
                                color: 'rgba(255,255,255,0.5)'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                color: 'rgba(255,255,255,0.7)',
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    }
                }
            });

            // Simulate chart data updates
            updateChartData();
        }

        function updateChartData() {
            if (!serverLoadChart) return;

            // Simulate CPU and memory data (in real app, fetch from API)
            const cpuData = serverLoadChart.data.datasets[0].data;
            const memData = serverLoadChart.data.datasets[1].data;

            cpuData.shift();
            cpuData.push(Math.floor(Math.random() * 40) + 20);

            memData.shift();
            memData.push(Math.floor(Math.random() * 30) + 15);

            serverLoadChart.update('none');
        }

        // ===== Escape HTML Helper =====
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ===== Initialize Dashboard =====
        async function initDashboard() {
            // Load all data
            await Promise.all([
                loadDashboardStats(),
                loadAlerts(),
                loadSystemStatus(),
                loadNetworkStats(),
                loadAICommandHistory()
            ]);

            // Setup interactions
            setupAICommandForm();
            initServerLoadChart();

            // Setup auto-refresh with polling indicator
            setInterval(() => {
                loadDashboardStats(true);
                loadAlerts();
                loadSystemStatus();
                loadNetworkStats();
            }, REFRESH_INTERVAL);

            // Update chart more frequently
            setInterval(updateChartData, 5000);
        }

        // Start dashboard
        initDashboard();
    });
</script>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
