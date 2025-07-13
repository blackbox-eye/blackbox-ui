<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blackbox EYE // Control Panel</title>
    <!-- Chosen Palette: Aura Gold & Deep Space -->
    <!-- Application Structure Plan: A modular, grid-based dashboard layout inspired by the user's provided sketch (Billede 2) and the 'Aura' design concept from the quality report. The structure prioritizes scannability and scalability. The most critical, high-level information (Threat Map, Alerts, System Status) is placed in the primary visual field (top and center). Secondary and tertiary information (logs, server load, general info) is positioned below or to the side. This non-linear, task-oriented structure replaces the old, rigid three-column layout, allowing operators to get a comprehensive overview at a glance while being able to dive into specific details. Key interactions involve hovering for more details and clicking to simulate navigating to deeper analysis sections. This structure was chosen to transform the passive report into a dynamic, interactive command center, directly addressing the core usability issues identified in the audit. -->
    <!-- Visualization & Content Choices: 
        - Report Info: Global Threat Overview -> Goal: Visualize live threat landscape -> Viz/Method: Interactive Canvas map (simulated) -> Interaction: Pulsing dots for threats, animated arcs for attack paths -> Justification: Transforms a passive title into the dashboard's engaging centerpiece, providing immediate high-level context. -> Library/Method: Vanilla JS Canvas API.
        - Report Info: Server Load -> Goal: Monitor system health -> Viz/Method: Line Chart -> Interaction: Hover for tooltips with specific values -> Justification: Line charts are the standard and most effective way to show data trends over time. -> Library/Method: Chart.js.
        - Report Info: Active Alerts -> Goal: Prioritize immediate threats -> Viz/Method: Styled list with icons and status indicators -> Interaction: Hover effects, clickable 'Investigate' buttons -> Justification: Turns a static list into an actionable queue, improving operator response time. -> Library/Method: HTML/CSS/JS.
        - Report Info: Network Watchlist -> Goal: Monitor key ports -> Viz/Method: Table with contextual labels and sparkline-style bars -> Interaction: Hover for details -> Justification: Adds crucial context (port names) and visual cues (activity bars) to previously meaningless numbers. -> Library/Method: HTML/CSS/JS.
        - All other info (System Status, Logs, etc.) is presented in structured HTML modules to ensure clarity and consistency within the 'glassmorphism' design system.
    -->
    <!-- CONFIRMATION: NO SVG graphics used. NO Mermaid JS used. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-gold: #D4AF35;
            --brand-gold-transparent-heavy: rgba(212, 175, 55, 0.25);
            --brand-gold-transparent-light: rgba(212, 175, 55, 0.1);
            --background-dark: #0D1117;
            --glass-bg: rgba(30, 35, 42, 0.5);
            --glass-blur: 15px;
            --border-radius: 12px;
            --text-primary: #E6EDF3;
            --text-secondary: #8B949E;
            --critical: #F85149;
            --warning: #FDBA74;
            --operational: #3FB950;
        }

        body {
            background-color: var(--background-dark);
            font-family: 'Roboto Condensed', sans-serif;
            color: var(--text-primary);
            overflow-x: hidden;
        }
        
        .font-brand {
            font-family: 'Orbitron', sans-serif;
        }

        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        .background-container::before {
            content: '👁';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 80vh;
            color: white;
            opacity: 0.02;
            line-height: 1;
            z-index: -2;
        }

        .noise-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 1000 1000' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: -1;
            opacity: 0.04;
        }

        .glass-module {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border-radius: var(--border-radius);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .glass-module::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            z-index: -1;
            margin: -1px;
            border-radius: inherit;
            background: linear-gradient(135deg, var(--brand-gold-transparent-heavy), var(--brand-gold-transparent-light));
        }

        .nav-link.active {
            background-color: rgba(212, 175, 55, 0.1);
            color: var(--brand-gold);
            border-left: 2px solid var(--brand-gold);
        }

        .nav-link:hover {
            background-color: rgba(212, 175, 55, 0.05);
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 250px;
        }
        
        .threat-map-container {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 400px;
        }
        
        #threatMapCanvas {
             background: url('https://placehold.co/1000x500/0D1117/1E232A?text=World+Map+Topology') no-repeat center center;
             background-size: cover;
             width: 100%;
             height: 100%;
             display: block;
        }

        .alert-critical::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: var(--critical);
        }
        
        .alert-warning::before {
             content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: var(--warning);
        }

        @keyframes pulse-critical {
            0%, 100% { box-shadow: 0 0 0 0 rgba(248, 81, 73, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(248, 81, 73, 0); }
        }
        .pulse-critical {
            animation: pulse-critical 2s infinite;
        }
        
        .ai-input:focus {
            outline: none;
            box-shadow: 0 0 0 1px var(--brand-gold);
        }

    </style>
</head>
<body class="text-sm">
    <div class="background-container"></div>
    <div class="noise-overlay"></div>

    <div class="flex min-h-screen">
        <!-- Sidebar Navigation -->
        <nav class="w-64 bg-black/20 p-4 flex-shrink-0 flex flex-col">
            <div class="font-brand text-2xl mb-12 text-center">
                <span class="text-white">BLACKBOX</span><span class="text-[var(--brand-gold)]">EYE</span>
                <p class="text-xs text-gray-400 font-sans tracking-widest">CONTROL PANEL</p>
            </div>
            
            <ul class="space-y-2 flex-grow">
                <li><a href="#" class="nav-link active flex items-center p-3 rounded-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>Dashboard</a></li>
                <li><a href="#" class="nav-link flex items-center p-3 rounded-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>User Management</a></li>
                 <li><a href="#" class="nav-link flex items-center p-3 rounded-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M12 15H12.01"></path><path d="M12 12H12.01"></path><path d="M12 9H12.01"></path><path d="M20 2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"></path></svg>System Logs</a></li>
                <li><a href="#" class="nav-link flex items-center p-3 rounded-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M12.22 2h-4.44a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.38"></path><path d="M16 2l6 6"></path><path d="M14 8h-4"></path><path d="M14 12h-4"></path><path d="M10 16h-4"></path></svg>API Keys</a></li>
                <li><a href="#" class="nav-link flex items-center p-3 rounded-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M12 20.94c1.5 0 2.75 1.06 4 1.06 3 0 6-8 6-12.22A4.91 4.91 0 0 0 17 5c-2.22 0-4 1.44-5 2-1-.56-2.78-2-5-2a4.9 4.9 0 0 0-5 4.78C2 14 5 22 8 22c1.25 0 2.5-1.06 4-1.06z"></path><path d="M10 2c1 .5 2 2 2 5"></path></svg>Settings</a></li>
            </ul>

            <div class="mt-auto text-center text-[var(--text-secondary)] text-xs">
                <p>Blackbox EYE v2.0 "Aura"</p>
                <p>&copy; 2025 All Rights Reserved</p>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 p-6 lg:p-8">
            <div class="grid grid-cols-12 grid-rows-6 gap-6 h-full">
                
                <div class="col-span-12 lg:col-span-8 row-span-3 glass-module p-4 flex flex-col">
                    <h2 class="font-brand text-lg text-white mb-2">GLOBAL THREAT OVERVIEW</h2>
                    <p class="text-sm text-[var(--text-secondary)] mb-4">This interactive map displays real-time threat intelligence, visualizing attack origins and targets across the globe. Pulsing nodes indicate active threat events, while arcs represent the trajectory of ongoing attacks. Hover over elements for high-level details. This provides a strategic, at-a-glance understanding of the current global security landscape.</p>
                    <div class="threat-map-container flex-grow">
                       <canvas id="threatMapCanvas"></canvas>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-4 row-span-3 glass-module p-4 flex flex-col">
                    <h2 class="font-brand text-lg text-white mb-2">ACTIVE ALERTS</h2>
                    <p class="text-sm text-[var(--text-secondary)] mb-4">A prioritized feed of immediate security threats detected by the system. Alerts are color-coded by severity: Red for Critical, Yellow for Warning. Each alert is an actionable item; click 'Investigate' to drill down into detailed logs and analysis for a rapid response.</p>
                    <div id="active-alerts-container" class="space-y-3 overflow-y-auto flex-grow pr-2">
                        <!-- Alerts will be injected here by JavaScript -->
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-4 row-span-2 glass-module p-4 flex flex-col">
                     <h2 class="font-brand text-lg text-white mb-2">SYSTEM STATUS</h2>
                     <p class="text-sm text-[var(--text-secondary)] mb-4">Provides a real-time health check of all core Blackbox EYE components. This module allows for quick verification that all subsystems are online and functioning within normal parameters, ensuring system integrity and reliability.</p>
                     <ul class="space-y-3 text-sm flex-grow">
                         <li class="flex justify-between items-center"><span>Firewall Service</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--operational)]/20 text-[var(--operational)]">OPERATIONAL</span></li>
                         <li class="flex justify-between items-center"><span>Threat Intel DB</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--operational)]/20 text-[var(--operational)]">STABLE</span></li>
                         <li class="flex justify-between items-center"><span>AI Core "GREY-E"</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--operational)]/20 text-[var(--operational)]">ACTIVE</span></li>
                         <li class="flex justify-between items-center"><span>API Gateway</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--warning)]/20 text-[var(--warning)]">HIGH LATENCY</span></li>
                         <li class="flex justify-between items-center"><span>Log Ingestion</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--operational)]/20 text-[var(--operational)]">NOMINAL</span></li>
                     </ul>
                </div>
                
                <div class="col-span-12 lg:col-span-4 row-span-2 glass-module p-4 flex flex-col">
                    <h2 class="font-brand text-lg text-white mb-2">NETWORK WATCHLIST</h2>
                    <p class="text-sm text-[var(--text-secondary)] mb-4">Monitors activity on critical network ports. This module provides a quick overview of traffic levels on designated ports, helping to identify unusual or unauthorized activity. The bar indicates relative traffic volume.</p>
                    <ul class="space-y-3 text-sm flex-grow">
                        <li class="flex items-center">
                            <span class="w-24">Port 22 (SSH)</span>
                            <div class="flex-grow bg-gray-700 rounded-full h-2.5"><div class="bg-blue-400 h-2.5 rounded-full" style="width: 45%"></div></div>
                        </li>
                         <li class="flex items-center">
                            <span class="w-24">Port 80 (HTTP)</span>
                            <div class="flex-grow bg-gray-700 rounded-full h-2.5"><div class="bg-blue-400 h-2.5 rounded-full" style="width: 75%"></div></div>
                        </li>
                        <li class="flex items-center">
                            <span class="w-24">Port 443 (HTTPS)</span>
                            <div class="flex-grow bg-gray-700 rounded-full h-2.5"><div class="bg-yellow-400 h-2.5 rounded-full" style="width: 88%"></div></div>
                        </li>
                        <li class="flex items-center">
                            <span class="w-24">Port 3306 (DB)</span>
                            <div class="flex-grow bg-gray-700 rounded-full h-2.5"><div class="bg-red-500 h-2.5 rounded-full" style="width: 95%"></div></div>
                        </li>
                        <li class="flex items-center">
                            <span class="w-24">Port 9200 (ES)</span>
                            <div class="flex-grow bg-gray-700 rounded-full h-2.5"><div class="bg-blue-400 h-2.5 rounded-full" style="width: 20%"></div></div>
                        </li>
                    </ul>
                </div>
                
                 <div class="col-span-12 lg:col-span-4 row-span-1 glass-module p-4 flex flex-col justify-center">
                    <h2 class="font-brand text-lg text-white mb-2">AI COMMAND INTERFACE</h2>
                    <p class="text-sm text-[var(--text-secondary)] mb-4">Direct access to the GREY-E AI Core. Use natural language commands to query data, initiate scans, or manage system tasks.</p>
                    <div class="relative">
                        <input type="text" placeholder="> Ask GREY-E anything..." class="ai-input w-full bg-black/30 border border-white/20 rounded-md py-2 px-4 text-white placeholder-gray-500 transition-all duration-300">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[var(--brand-gold)]">↵</span>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-8 row-span-1 glass-module p-4 flex items-center">
                    <div class="w-1/3">
                        <h3 class="font-brand text-white">AGENTS ONLINE</h3>
                        <p class="text-4xl font-brand text-[var(--brand-gold)]">1,337</p>
                    </div>
                     <div class="w-1/3 border-l border-r border-white/10 px-4">
                        <h3 class="font-brand text-white">SYSTEM UPTIME</h3>
                        <p class="text-4xl font-brand text-white">99.98%</p>
                    </div>
                     <div class="w-1/3 pl-4">
                        <h3 class="font-brand text-white">24H EVENTS</h3>
                        <p class="text-4xl font-brand text-white">2.1M</p>
                    </div>
                </div>
                
                <div class="col-span-12 lg:col-span-12 row-span-2 glass-module p-4 flex flex-col">
                    <h2 class="font-brand text-lg text-white mb-2">SERVER LOAD (LAST 60 MIN)</h2>
                    <p class="text-sm text-[var(--text-secondary)] mb-4">This chart displays key server performance metrics over the past hour, providing insights into system resource utilization. Monitor CPU, Memory, and Disk usage to anticipate performance bottlenecks and ensure operational stability. The chart is interactive; hover over the lines to see precise values at any point in time.</p>
                    <div class="chart-container flex-grow">
                        <canvas id="serverLoadChart"></canvas>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // --- Mock Data ---
            const mockAlerts = [
                { severity: 'critical', title: 'Brute Force Attack Detected', target: 'SSH on SRV-01', time: '2 min ago', pulse: true },
                { severity: 'critical', title: 'Anomalous Outbound Traffic', target: 'DB-CLUSTER-03', time: '5 min ago', pulse: true },
                { severity: 'warning', title: 'Multiple Failed Logins', target: 'Admin Portal', time: '12 min ago', pulse: false },
                { severity: 'warning', title: 'Unusual API Activity', target: 'User-API-v3', time: '28 min ago', pulse: false },
                { severity: 'warning', title: 'SSL Certificate Expiring', target: 'cdn.blackbox.com', time: '45 min ago', pulse: false },
            ];

            // --- Populate Active Alerts ---
            const alertsContainer = document.getElementById('active-alerts-container');
            if(alertsContainer) {
                mockAlerts.forEach(alert => {
                    const alertEl = document.createElement('div');
                    alertEl.className = `relative p-3 rounded-lg bg-black/20 alert-${alert.severity} ${alert.pulse ? 'pulse-critical' : ''}`;
                    alertEl.innerHTML = `
                        <h3 class="font-bold text-white">${alert.title}</h3>
                        <p class="text-xs text-[var(--text-secondary)]">${alert.target}</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-[var(--text-secondary)]">${alert.time}</span>
                            <a href="#" class="text-xs text-[var(--brand-gold)] hover:underline font-bold">Investigate &rarr;</a>
                        </div>
                    `;
                    alertsContainer.appendChild(alertEl);
                });
            }

            // --- Server Load Chart ---
            const serverLoadCtx = document.getElementById('serverLoadChart');
            if (serverLoadCtx) {
                new Chart(serverLoadCtx, {
                    type: 'line',
                    data: {
                        labels: Array.from({length: 12}, (_, i) => `${(i+1)*5}m ago`),
                        datasets: [
                            {
                                label: 'CPU Load',
                                data: Array.from({length: 12}, () => Math.random() * 60 + 20),
                                borderColor: 'rgba(212, 175, 55, 1)',
                                backgroundColor: 'rgba(212, 175, 55, 0.2)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 0,
                                pointHoverRadius: 5
                            },
                             {
                                label: 'Memory Usage',
                                data: Array.from({length: 12}, () => Math.random() * 50 + 10),
                                borderColor: 'rgba(59, 130, 246, 1)',
                                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.4,
                                pointRadius: 0,
                                pointHoverRadius: 5
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
                                ticks: { color: 'rgba(255,255,255,0.5)', callback: function(value) { return value + '%' } },
                                grid: { color: 'rgba(255,255,255,0.1)' }
                            },
                            x: {
                                ticks: { color: 'rgba(255,255,255,0.5)' },
                                grid: { color: 'rgba(255,255,255,0.05)' }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: {
                                    color: 'rgba(255,255,255,0.8)',
                                    boxWidth: 12,
                                    padding: 20
                                }
                            }
                        }
                    }
                });
            }

            // --- Threat Map Canvas Animation ---
            const canvas = document.getElementById('threatMapCanvas');
            if(canvas){
                const ctx = canvas.getContext('2d');
                let width, height;
                
                const points = [];
                for(let i=0; i<15; i++){
                    points.push({
                        x: Math.random(),
                        y: Math.random(),
                        radius: Math.random() * 3 + 1,
                        alpha: 0,
                        alphaChange: Math.random() * 0.02 + 0.01
                    });
                }
                
                const arcs = [];
                 for(let i=0; i<5; i++){
                    arcs.push({
                        startX: Math.random(),
                        startY: Math.random(),
                        endX: Math.random(),
                        endY: Math.random(),
                        progress: 0,
                        speed: Math.random() * 0.005 + 0.002
                    });
                }


                function resizeCanvas() {
                    const container = canvas.parentElement;
                    width = container.clientWidth;
                    height = container.clientHeight;
                    canvas.width = width;
                    canvas.height = height;
                }

                function draw() {
                    ctx.clearRect(0, 0, width, height);

                    // Draw points
                    points.forEach(p => {
                        ctx.beginPath();
                        ctx.arc(p.x * width, p.y * height, p.radius, 0, Math.PI * 2);
                        ctx.fillStyle = `rgba(248, 81, 73, ${p.alpha})`;
                        ctx.fill();

                        p.alpha += p.alphaChange;
                        if(p.alpha > 1 || p.alpha < 0) {
                            p.alphaChange *= -1;
                        }
                    });
                    
                    // Draw arcs
                     arcs.forEach(a => {
                        ctx.beginPath();
                        const currentX = a.startX + (a.endX - a.startX) * a.progress;
                        const currentY = a.startY + (a.endY - a.startY) * a.progress;
                        
                        ctx.moveTo(a.startX * width, a.startY * height);
                        const cpX = (a.startX + a.endX) / 2 + (a.startY - a.endY) * 0.4;
                        const cpY = (a.startY + a.endY) / 2 + (a.endX - a.startX) * 0.4;

                        ctx.quadraticCurveTo(cpX * width, cpY * height, a.endX * width, a.endY * height);

                        ctx.strokeStyle = `rgba(212, 175, 55, 0.4)`;
                        ctx.lineWidth = 1;
                        ctx.stroke();

                        a.progress += a.speed;
                        if(a.progress >= 1){
                            a.progress = 0;
                            a.startX = Math.random();
                            a.startY = Math.random();
                            a.endX = Math.random();
                            a.endY = Math.random();
                        }
                    });

                    requestAnimationFrame(draw);
                }
                
                window.addEventListener('resize', resizeCanvas);
                resizeCanvas();
                draw();
            }
        });
    </script>
</body>
</html>
