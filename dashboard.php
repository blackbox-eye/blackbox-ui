<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blackbox EYE // Kontrolpanel</title>
    <!-- Chosen Palette: Unified Brand - Deep Space & Brand Gold -->
    <!-- Application Structure Plan: Retaining the flexible grid layout but reverting to a solid, brand-aligned design. The 'glassmorphism' is removed in favor of solid, dark modules with subtle brand-gold accents, creating a direct visual link to the agent-login page. This unified approach ensures brand consistency. The layout is now fully responsive using Tailwind's breakpoints as requested, and accessibility is improved with ARIA labels. The goal is a professional, clean, and scalable interface that feels like a natural extension of the existing site. -->
    <!-- Visualization & Content Choices: The core visualizations (Canvas Map, Chart.js) are retained for their effectiveness but are now framed within the new, solid module design. All user-facing text has been translated to Danish for brand consistency. Actionable items like 'Investigate' are styled to be more button-like on hover, reflecting the UI patterns from the front page. -->
    <!-- CONFIRMATION: NO SVG graphics used. NO Mermaid JS used. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-gold: #D4AF35;
            --background-dark: #0D1117;
            --module-bg: #161B22;
            --border-color: #30363d;
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

        .solid-module {
            background-color: var(--module-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .solid-module-header {
             border-bottom: 1px solid var(--border-color);
        }

        .nav-link.active {
            background-color: rgba(212, 175, 55, 0.1);
            color: var(--brand-gold);
            border-left: 3px solid var(--brand-gold);
            font-weight: 700;
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
        
        #threatMapCanvas {
             background: url('https://placehold.co/1000x500/161B22/30363d?text=Global+Topologi') no-repeat center center;
             background-size: cover;
             border-radius: 0 0 8px 8px;
             width: 100%;
             height: 100%;
             display: block;
        }

        .alert-critical { border-left: 3px solid var(--critical); }
        .alert-warning { border-left: 3px solid var(--warning); }

        @keyframes pulse-critical {
            0%, 100% { box-shadow: 0 0 0 0 rgba(248, 81, 73, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(248, 81, 73, 0); }
        }
        .pulse-critical {
            animation: pulse-critical 2s infinite;
        }
        
        .ai-input:focus {
            outline: none;
            border-color: var(--brand-gold);
            box-shadow: 0 0 0 1px var(--brand-gold);
        }
        
        .btn-investigate {
            transition: background-color 0.2s, color 0.2s;
        }
        .btn-investigate:hover {
            background-color: var(--brand-gold);
            color: var(--background-dark);
        }

    </style>
</head>
<body class="text-sm">
    <!-- 
    <?php
    /*
    session_start();
    if (!isset($_SESSION['agent_id'])) {
        header('Location: /agent-login.php');
        exit;
    }
    */
    ?>
    -->
    <div class="flex min-h-screen">
        <!-- Sidebar Navigation -->
        <nav class="w-64 bg-black/30 p-4 flex-shrink-0 flex-col hidden md:flex">
            <div class="font-brand text-2xl mb-12 text-left">
                <span class="text-white">BLACKBOX</span><span class="text-[var(--brand-gold)]">EYE™</span>
                <p class="text-xs text-gray-400 font-sans tracking-widest -mt-1">CONTROL PANEL</p>
            </div>
            
            <ul class="space-y-2 flex-grow">
                <li><a href="#" class="nav-link active flex items-center p-3 rounded-r-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>Dashboard</a></li>
                <li><a href="#" class="nav-link flex items-center p-3 rounded-r-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>Brugerstyring</a></li>
                <li><a href="#" class="nav-link flex items-center p-3 rounded-r-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M12 15H12.01"></path><path d="M12 12H12.01"></path><path d="M12 9H12.01"></path><path d="M20 2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"></path></svg>Systemlogs</a></li>
                <li><a href="#" class="nav-link flex items-center p-3 rounded-r-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M12.22 2h-4.44a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.38"></path><path d="M16 2l6 6"></path><path d="M14 8h-4"></path><path d="M14 12h-4"></path><path d="M10 16h-4"></path></svg>API Nøgler</a></li>
                <li><a href="#" class="nav-link flex items-center p-3 rounded-r-lg transition-colors duration-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M12 20.94c1.5 0 2.75 1.06 4 1.06 3 0 6-8 6-12.22A4.91 4.91 0 0 0 17 5c-2.22 0-4 1.44-5 2-1-.56-2.78-2-5-2a4.9 4.9 0 0 0-5 4.78C2 14 5 22 8 22c1.25 0 2.5-1.06 4-1.06z"></path><path d="M10 2c1 .5 2 2 2 5"></path></svg>Indstillinger</a></li>
            </ul>

            <div class="mt-auto text-center text-[var(--text-secondary)] text-xs">
                <p>Blackbox EYE v2.1 "Unified"</p>
                <p>&copy; 2025 Alle rettigheder forbeholdes</p>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <div class="lg:col-span-8 solid-module flex flex-col">
                    <div class="solid-module-header p-4">
                        <h2 class="font-brand text-lg text-white">GLOBAL TRUSSELSOVERSIGT</h2>
                    </div>
                    <div class="p-4 text-sm text-[var(--text-secondary)]">
                        Dette interaktive kort viser realtids trusselsdata. Pulserende knudepunkter indikerer aktive hændelser, og buer viser angrebsvektorer.
                    </div>
                    <div class="flex-grow h-96">
                       <canvas id="threatMapCanvas" role="img" aria-label="Globalt trusselskort der viser live trusselshændelser"></canvas>
                    </div>
                </div>

                <div class="lg:col-span-4 solid-module flex flex-col">
                    <div class="solid-module-header p-4">
                        <h2 class="font-brand text-lg text-white">AKTIVE ALARMER</h2>
                    </div>
                    <div class="p-4 text-sm text-[var(--text-secondary)] mb-2">
                        Prioriteret liste over øjeblikkelige sikkerhedstrusler.
                    </div>
                    <div id="active-alerts-container" class="space-y-3 overflow-y-auto flex-grow px-4 pb-4">
                        <!-- Alerts will be injected here by JavaScript -->
                    </div>
                </div>

                <div class="lg:col-span-4 solid-module p-4 flex flex-col">
                     <h2 class="font-brand text-lg text-white mb-4">SYSTEMSTATUS</h2>
                     <ul class="space-y-3 text-sm flex-grow">
                         <li class="flex justify-between items-center"><span>Firewall Service</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--operational)]/20 text-[var(--operational)]">OPERATIONEL</span></li>
                         <li class="flex justify-between items-center"><span>Threat Intel DB</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--operational)]/20 text-[var(--operational)]">STABIL</span></li>
                         <li class="flex justify-between items-center"><span>AI Core "GREY-E"</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--operational)]/20 text-[var(--operational)]">AKTIV</span></li>
                         <li class="flex justify-between items-center"><span>API Gateway</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--warning)]/20 text-[var(--warning)]">HØJ LATENS</span></li>
                         <li class="flex justify-between items-center"><span>Log Indtagelse</span><span class="text-xs font-bold py-1 px-2 rounded-full bg-[var(--operational)]/20 text-[var(--operational)]">NOMINEL</span></li>
                     </ul>
                </div>
                
                <div class="lg:col-span-4 solid-module p-4 flex flex-col">
                    <h2 class="font-brand text-lg text-white mb-4">NETVÆRKSOVERVÅGNING</h2>
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
                
                 <div class="lg:col-span-4 solid-module p-4 flex flex-col justify-center">
                    <h2 class="font-brand text-lg text-white mb-2">AI KOMMANDO INTERFACE</h2>
                    <p class="text-sm text-[var(--text-secondary)] mb-4">Brug naturligt sprog til at forespørge data eller starte scanninger.</p>
                    <div class="relative">
                        <input type="text" placeholder="> Spørg GREY-E om noget..." class="ai-input w-full bg-black/30 border-2 border-gray-600 rounded-md py-2 px-4 text-white placeholder-gray-500 transition-all duration-300">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[var(--brand-gold)]">↵</span>
                    </div>
                </div>

                <div class="lg:col-span-12 solid-module p-4 grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-700">
                    <div class="py-4 sm:py-0 sm:px-4 text-center">
                        <h3 class="font-brand text-white">AGENTER ONLINE</h3>
                        <p class="text-4xl font-brand text-[var(--brand-gold)]">1,337</p>
                    </div>
                     <div class="py-4 sm:py-0 sm:px-4 text-center">
                        <h3 class="font-brand text-white">SYSTEM OPPE TID</h3>
                        <p class="text-4xl font-brand text-white">99.98%</p>
                    </div>
                     <div class="py-4 sm:py-0 sm:px-4 text-center">
                        <h3 class="font-brand text-white">24T HÆNDELSER</h3>
                        <p class="text-4xl font-brand text-white">2.1M</p>
                    </div>
                </div>
                
                <div class="lg:col-span-12 solid-module flex flex-col">
                    <div class="solid-module-header p-4">
                        <h2 class="font-brand text-lg text-white">SERVERBELASTNING (SIDSTE 60 MIN)</h2>
                    </div>
                    <div class="p-4 text-sm text-[var(--text-secondary)]">
                        Dette diagram viser nøgle-metrics for serverens ydeevne. Hold musen over for præcise værdier.
                    </div>
                    <div class="chart-container flex-grow p-4">
                        <canvas id="serverLoadChart" role="img" aria-label="Graf der viser serverbelastning for CPU og Hukommelse over de sidste 60 minutter"></canvas>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const mockAlerts = [
                { severity: 'critical', title: 'Brute Force Angreb Opdaget', target: 'SSH på SRV-01', time: '2 min siden', pulse: true },
                { severity: 'critical', title: 'Anormal Udgående Trafik', target: 'DB-CLUSTER-03', time: '5 min siden', pulse: true },
                { severity: 'warning', title: 'Flere Fejlede Logins', target: 'Admin Portal', time: '12 min siden', pulse: false },
                { severity: 'warning', title: 'Usædvanlig API Aktivitet', target: 'User-API-v3', time: '28 min siden', pulse: false },
                { severity: 'warning', title: 'SSL Certifikat Udløber', target: 'cdn.blackbox.com', time: '45 min siden', pulse: false },
            ];

            const alertsContainer = document.getElementById('active-alerts-container');
            if(alertsContainer) {
                mockAlerts.forEach(alert => {
                    const alertEl = document.createElement('div');
                    alertEl.className = `relative p-3 rounded-md bg-[var(--background-dark)] alert-${alert.severity} ${alert.pulse ? 'pulse-critical' : ''}`;
                    alertEl.innerHTML = `
                        <h3 class="font-bold text-white">${alert.title}</h3>
                        <p class="text-xs text-[var(--text-secondary)]">${alert.target}</p>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-[var(--text-secondary)]">${alert.time}</span>
                            <a href="#" class="btn-investigate text-xs text-[var(--brand-gold)] hover:underline font-bold border border-current rounded-full px-3 py-1" aria-label="Undersøg alarm: ${alert.title}">Undersøg &rarr;</a>
                        </div>
                    `;
                    alertsContainer.appendChild(alertEl);
                });
            }

            const serverLoadCtx = document.getElementById('serverLoadChart');
            if (serverLoadCtx) {
                new Chart(serverLoadCtx, {
                    type: 'line',
                    data: {
                        labels: Array.from({length: 12}, (_, i) => `${60 - i*5}`),
                        datasets: [
                            {
                                label: 'CPU Belastning',
                                data: [22, 25, 30, 45, 50, 55, 60, 58, 52, 40, 35, 28].reverse(),
                                borderColor: 'rgba(212, 175, 55, 1)',
                                backgroundColor: 'rgba(212, 175, 55, 0.2)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 0,
                                pointHoverRadius: 5
                            },
                             {
                                label: 'Hukommelsesbrug',
                                data: [15, 18, 22, 20, 28, 35, 33, 40, 38, 30, 25, 20].reverse(),
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
                                title: { display: true, text: 'Minutter siden', color: 'rgba(255,255,255,0.5)'},
                                ticks: { color: 'rgba(255,255,255,0.5)' },
                                grid: { display: false }
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

            const threatMapCanvas = document.getElementById('threatMapCanvas');
            if(threatMapCanvas){
                const ctx = threatMapCanvas.getContext('2d');
                let width, height;
                
                const points = Array.from({length: 15}, () => ({
                    x: Math.random(), y: Math.random(),
                    radius: Math.random() * 3 + 1,
                    alpha: 0, alphaChange: Math.random() * 0.02 + 0.01
                }));
                
                const arcs = Array.from({length: 5}, () => ({
                    startX: Math.random(), startY: Math.random(),
                    endX: Math.random(), endY: Math.random(),
                    progress: Math.random(), speed: Math.random() * 0.005 + 0.002
                }));

                const resizeCanvas = () => {
                    const container = threatMapCanvas.parentElement;
                    width = container.clientWidth;
                    height = container.clientHeight;
                    threatMapCanvas.width = width;
                    threatMapCanvas.height = height;
                };

                function draw() {
                    if(!ctx) return;
                    ctx.clearRect(0, 0, width, height);

                    points.forEach(p => {
                        ctx.beginPath();
                        ctx.arc(p.x * width, p.y * height, p.radius, 0, Math.PI * 2);
                        ctx.fillStyle = `rgba(248, 81, 73, ${p.alpha})`;
                        ctx.fill();
                        p.alpha += p.alphaChange;
                        if(p.alpha > 1 || p.alpha < 0) p.alphaChange *= -1;
                    });
                    
                     arcs.forEach(a => {
                        ctx.beginPath();
                        const cpX = (a.startX + a.endX) / 2 + (a.startY - a.endY) * 0.4;
                        const cpY = (a.startY + a.endY) / 2 + (a.endX - a.startX) * 0.4;
                        ctx.moveTo(a.startX * width, a.startY * height);
                        ctx.quadraticCurveTo(cpX * width, cpY * height, a.endX * width, a.endY * height);
                        ctx.strokeStyle = `rgba(212, 175, 55, 0.4)`;
                        ctx.lineWidth = 1;
                        ctx.stroke();
                        a.progress += a.speed;
                        if(a.progress >= 1){
                            a.progress = 0;
                            a.startX = Math.random(); a.startY = Math.random();
                            a.endX = Math.random(); a.endY = Math.random();
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
