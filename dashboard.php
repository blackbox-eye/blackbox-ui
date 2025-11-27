<?php

/**
 * Dashboard - GreyEYE Command Center
 *
 * Modern dashboard interface with responsive card-based layout.
 * Uses admin-layout.php for consistent navigation via Command Deck.
 * Displays alerts, system status, network monitoring, and AI interface.
 */

session_start();
if (empty($_SESSION['agent_id'])) {
    header('Location: agent-login.php');
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
        --dash-warning: #fbbf24;
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
        transition: border-color 0.2s;
    }

    .dashboard__stat:hover {
        border-color: var(--admin-border-gold);
    }

    .dashboard__stat-value {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--admin-text-gold);
        line-height: 1;
    }

    .dashboard__stat-label {
        font-size: 0.68rem;
        color: var(--admin-text-muted);
        margin-top: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

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
            <div class="dashboard__stat-value" id="statAlerts">3</div>
            <div class="dashboard__stat-label">Aktive Alarmer</div>
        </div>
        <div class="dashboard__stat">
            <div class="dashboard__stat-value" id="statThreats">12</div>
            <div class="dashboard__stat-label">Trusler i dag</div>
        </div>
        <div class="dashboard__stat">
            <div class="dashboard__stat-value" id="statUptime">99.8%</div>
            <div class="dashboard__stat-label">System Uptime</div>
        </div>
        <div class="dashboard__stat">
            <div class="dashboard__stat-value" id="statRequests">1.2K</div>
            <div class="dashboard__stat-label">API Requests</div>
        </div>
    </div>

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
                <span class="dashboard__card-badge dashboard__card-badge--critical">2 Kritiske</span>
            </header>
            <div id="alertsContainer">
                <!-- Alerts populated by JS -->
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
            </header>
            <ul class="dashboard__status-list">
                <li class="dashboard__status-item">
                    <span class="dashboard__status-label">Firewall Service</span>
                    <span class="dashboard__status-badge dashboard__status-badge--ok">Operationel</span>
                </li>
                <li class="dashboard__status-item">
                    <span class="dashboard__status-label">Threat Intel DB</span>
                    <span class="dashboard__status-badge dashboard__status-badge--ok">Stabil</span>
                </li>
                <li class="dashboard__status-item">
                    <span class="dashboard__status-label">AI Core "GREY-E"</span>
                    <span class="dashboard__status-badge dashboard__status-badge--ok">Aktiv</span>
                </li>
                <li class="dashboard__status-item">
                    <span class="dashboard__status-label">API Gateway</span>
                    <span class="dashboard__status-badge dashboard__status-badge--warning">Høj Latens</span>
                </li>
            </ul>
        </div>

        <!-- Network Monitoring Card -->
        <div class="dashboard__card dashboard__card--network">
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
            <div>
                <div class="dashboard__network-item">
                    <div class="dashboard__network-header">
                        <span class="dashboard__network-label">Port 22 (SSH)</span>
                        <span class="dashboard__network-value">45%</span>
                    </div>
                    <div class="dashboard__network-bar">
                        <div class="dashboard__network-fill dashboard__network-fill--low" style="width: 45%"></div>
                    </div>
                </div>
                <div class="dashboard__network-item">
                    <div class="dashboard__network-header">
                        <span class="dashboard__network-label">Port 443 (HTTPS)</span>
                        <span class="dashboard__network-value">88%</span>
                    </div>
                    <div class="dashboard__network-bar">
                        <div class="dashboard__network-fill dashboard__network-fill--medium" style="width: 88%"></div>
                    </div>
                </div>
                <div class="dashboard__network-item">
                    <div class="dashboard__network-header">
                        <span class="dashboard__network-label">Port 3306 (DB)</span>
                        <span class="dashboard__network-value">95%</span>
                    </div>
                    <div class="dashboard__network-bar">
                        <div class="dashboard__network-fill dashboard__network-fill--high" style="width: 95%"></div>
                    </div>
                </div>
                <div class="dashboard__network-item">
                    <div class="dashboard__network-header">
                        <span class="dashboard__network-label">Port 9200 (ES)</span>
                        <span class="dashboard__network-value">20%</span>
                    </div>
                    <div class="dashboard__network-bar">
                        <div class="dashboard__network-fill dashboard__network-fill--low" style="width: 20%"></div>
                    </div>
                </div>
            </div>
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
        <div class="dashboard__card dashboard__card--ai">
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
            <textarea
                class="dashboard__ai-input"
                placeholder="> Analysér trafik fra IP 192.168.1.100..."></textarea>
            <p class="dashboard__ai-hint">
                Tryk <kbd>Enter</kbd> for at sende kommando • <kbd>Shift+Enter</kbd> for ny linje
            </p>
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
        // Mock alert data
        const mockAlerts = [{
                id: 'a1',
                severity: 'critical',
                title: 'Brute Force Angreb Opdaget',
                target: 'SSH på SRV-01',
                time: '2 min siden'
            },
            {
                id: 'a2',
                severity: 'critical',
                title: 'Anormal Udgående Trafik',
                target: 'DB-CLUSTER-03',
                time: '5 min siden'
            },
            {
                id: 'a3',
                severity: 'warning',
                title: 'Flere Fejlede Logins',
                target: 'Admin Portal',
                time: '12 min siden'
            },
        ];

        // Populate alerts
        const alertsContainer = document.getElementById('alertsContainer');
        if (alertsContainer) {
            mockAlerts.forEach(alert => {
                const alertEl = document.createElement('div');
                alertEl.className = `dashboard__alert ${alert.severity === 'critical' ? 'dashboard__alert--critical' : ''}`;
                alertEl.innerHTML = `
                <h3 class="dashboard__alert-title">${alert.title}</h3>
                <div class="dashboard__alert-meta">
                    <span>${alert.time} • ${alert.target}</span>
                    <a href="#" class="dashboard__alert-action" data-alert-id="${alert.id}">Undersøg →</a>
                </div>
            `;
                alertsContainer.appendChild(alertEl);
            });
        }

        // Server Load Chart
        const serverLoadCtx = document.getElementById('serverLoadChart');
        if (serverLoadCtx) {
            new Chart(serverLoadCtx, {
                type: 'line',
                data: {
                    labels: Array.from({
                        length: 12
                    }, (_, i) => `${60 - i * 5}m`),
                    datasets: [{
                            label: 'CPU Belastning',
                            data: [22, 25, 30, 45, 50, 55, 60, 58, 52, 40, 35, 28].reverse(),
                            borderColor: 'rgba(212, 175, 55, 1)',
                            backgroundColor: 'rgba(212, 175, 55, 0.15)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        },
                        {
                            label: 'Hukommelsesbrug',
                            data: [15, 18, 22, 20, 28, 35, 33, 40, 38, 30, 25, 20].reverse(),
                            borderColor: 'rgba(96, 165, 250, 1)',
                            backgroundColor: 'rgba(96, 165, 250, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4,
                            pointRadius: 0
                        }
                    ]
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
        }
    });
</script>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
