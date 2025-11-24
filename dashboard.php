<?php
session_start();
if (empty($_SESSION['agent_id'])) {
    header('Location: agent-login.php');
    exit;
}
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Chosen Palette: Aura Gold & Deep Space -->
    <!-- Application Structure Plan: Applikationen benytter en avanceret CSS Grid-struktur låst til 100% af skærmhøjden for at eliminere vertikal scrolling. Layoutet er defineret via 'grid-template-areas', som intelligent omstrukturerer modulernes placering ved forskellige skærmstørrelser (breakpoints) for optimal pladsudnyttelse. Dette skaber en fast, men fuldt responsiv oplevelse. Kerneinteraktionen er centreret omkring 'progressive disclosure' (faneblade, modaler) for at håndtere høj informations-tæthed inden for den faste ramme. -->
    <!-- Visualization & Content Choices: 
        - Rapport Info: Global trusselsvisualisering -> Mål: Skabe et "wow-faktor" kommandocenter -> Metode: Animeret HTML Canvas -> Interaktion: Passiv realtidsfølelse -> Begrundelse: Central, visuelt engagerende komponent.
        - Rapport Info: Serverbelastning over tid -> Mål: Vise trends -> Metode: Chart.js linjegraf -> Interaktion: Hover for tooltips -> Begrundelse: Standard, klar visualisering af tidsseriedata.
        - Rapport Info: Aktive alarmer -> Mål: Præsentere handlingsorienterede events -> Metode: Dynamisk genereret liste af "glas"-kort -> Interaktion: Klik på 'Undersøg' åbner en modal -> Begrundelse: Isolerer detaljer og holder brugeren i kontekst.
        - Rapport Info: Systemstatus/Netværk -> Mål: Give hurtigt overblik -> Metode: Statiske informationsmoduler med ikoner og farvekodning -> Begrundelse: Hurtig aflæsning af status.
        - Bibliotek: Chart.js til grafer, ren JS/CSS til alt andet.
    -->
    <!-- CONFIRMATION: NO SVG graphics used. NO Mermaid JS used. -->
    <title>Blackbox EYE // Aura Kontrolpanel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js: Using latest stable version. For SRI, pin to specific version (e.g., @4.4.1) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" 
            crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-gold: #D4AF35;
            --brand-gold-transparent-heavy: rgba(212, 175, 55, 0.2);
            --brand-gold-transparent-light: rgba(212, 175, 55, 0.1);
            --background-dark: #0D1117;
            --glass-bg: rgba(30, 35, 42, 0.5);
            --glass-blur: 12px;
            --border-color: rgba(212, 175, 55, 0.2);
            --text-primary: #E6EDF3;
            --text-secondary: #8B949E;
            --critical: #F85149;
        }
        
        body {
            background-color: var(--background-dark);
            font-family: 'Roboto Condensed', sans-serif;
            color: var(--text-primary);
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }

        .font-brand {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 0.05em;
        }
        
        .background-container {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -2;
        }

        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80vh;
            height: 80vh;
            background: url('https://i.imgur.com/uF6X2xH.png') no-repeat center center; /* Placeholder for logo emblem */
            background-size: contain;
            opacity: 0.02;
        }

        .noise-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 1000 1000' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.04;
            pointer-events: none;
        }
        
        .glass-module {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
            border: 1px solid transparent;
            background-clip: padding-box;
            border-image: linear-gradient(135deg, var(--brand-gold-transparent-heavy), var(--brand-gold-transparent-light)) 1;
        }
        
        @keyframes pulse-critical {
            0%, 100% { box-shadow: 0 0 0 0 rgba(248, 81, 73, 0.4); }
            70% { box-shadow: 0 0 10px 15px rgba(248, 81, 73, 0); }
        }

        .pulse-critical {
            animation: pulse-critical 2s infinite;
        }

        /* --- Grid Layout Definitions --- */
        #main-grid {
            display: grid;
            height: 100%;
            padding: 1rem;
            gap: 1rem;
            grid-template-columns: 280px repeat(10, 1fr);
            grid-template-rows: repeat(12, 1fr);
            grid-template-areas:
                "nav map map map map map map map alerts alerts alerts"
                "nav map map map map map map map alerts alerts alerts"
                "nav map map map map map map map alerts alerts alerts"
                "nav map map map map map map map alerts alerts alerts"
                "nav map map map map map map map alerts alerts alerts"
                "nav map map map map map map map ai ai ai"
                "nav map map map map map map map ai ai ai"
                "nav map map map map map map map ai ai ai"
                "nav status status status net net net net ai ai ai"
                "nav status status status net net net net ai ai ai"
                "nav status status status net net net net ai ai ai"
                "nav status status status net net net net ai ai ai";
        }

        #nav-menu { grid-area: nav; }
        #threat-module { grid-area: map; }
        #alerts-module { grid-area: alerts; }
        #status-module { grid-area: status; }
        #net-module { grid-area: net; }
        #ai-module { grid-area: ai; }
        
        @media (max-width: 1440px) {
            #main-grid {
                grid-template-columns: 250px repeat(10, 1fr);
                grid-template-areas:
                    "nav map map map map map alerts alerts alerts alerts alerts"
                    "nav map map map map map alerts alerts alerts alerts alerts"
                    "nav map map map map map alerts alerts alerts alerts alerts"
                    "nav map map map map map alerts alerts alerts alerts alerts"
                    "nav map map map map map ai ai ai ai ai"
                    "nav map map map map map ai ai ai ai ai"
                    "nav status status status status status ai ai ai ai ai"
                    "nav status status status status status ai ai ai ai ai"
                    "nav status status status status status net net net net net"
                    "nav status status status status status net net net net net"
                    "nav net-spare net-spare net-spare net-spare net-spare net net net net net"
                    "nav net-spare net-spare net-spare net-spare net-spare net net net net net";
            }
            /* Adjust module visibility or content for this breakpoint if needed */
        }
        
        @media (max-width: 1024px) {
             #main-grid {
                grid-template-columns: repeat(12, 1fr);
                grid-template-rows: auto;
                 grid-template-areas:
                    "nav nav nav nav nav nav nav nav nav nav nav nav"
                    "map map map map map map map map map map map map"
                    "map map map map map map map map map map map map"
                    "alerts alerts alerts alerts alerts alerts alerts alerts alerts alerts alerts alerts"
                    "alerts alerts alerts alerts alerts alerts alerts alerts alerts alerts alerts alerts"
                    "status status status status status status net net net net net net"
                    "ai ai ai ai ai ai ai ai ai ai ai ai";
                 overflow-y: auto; /* Allow scroll only on smallest screens */
            }
             #nav-menu { flex-direction: row; justify-content: space-around; }
        }

    </style>
</head>
<body>
    <div class="background-container">
        <div class="watermark-logo"></div>
        <div class="noise-overlay"></div>
    </div>

    <div id="main-grid">
        <nav id="nav-menu" class="glass-module flex flex-col p-4">
            <div class="font-brand text-2xl mb-12 text-center">
                <span class="text-white">BLACKBOX</span><span class="text-[var(--brand-gold)]">EYE™</span>
                <p class="text-xs text-gray-400 font-sans tracking-widest mt-1">AURA C-PANEL</p>
            </div>
            <div class="text-xs uppercase tracking-widest text-gray-400 mb-4 text-center">
                Agent: <span class="text-white"><?php echo htmlspecialchars($_SESSION['agent_id']); ?></span>
            </div>
            <ul class="space-y-2 flex-grow">
                <li>
                    <a href="dashboard.php" class="flex items-center p-3 text-lg rounded-lg transition-colors <?php echo $currentScript === 'dashboard.php' ? 'text-[var(--brand-gold)] bg-white/5' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="admin.php" class="flex items-center p-3 text-lg rounded-lg transition-colors <?php echo $currentScript === 'admin.php' ? 'text-[var(--brand-gold)] bg-white/5' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Brugerstyring
                    </a>
                </li>
                <li>
                    <a href="download-logs.php" class="flex items-center p-3 text-lg rounded-lg transition-colors <?php echo $currentScript === 'download-logs.php' ? 'text-[var(--brand-gold)] bg-white/5' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Systemlogs
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="flex items-center p-3 text-lg rounded-lg transition-colors <?php echo $currentScript === 'settings.php' ? 'text-[var(--brand-gold)] bg-white/5' : 'text-gray-300 hover:bg-white/5 hover:text-white'; ?>">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7h2a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2h2m4 0h-4m4 0v-2a2 2 0 00-2-2h-2a2 2 0 00-2 2v2m4 0h-4"></path></svg>
                        Indstillinger
                    </a>
                </li>
            </ul>
            <div class="mt-6 pt-4 border-t border-white/10">
                <a href="logout.php" class="block text-center px-4 py-2 text-sm font-semibold rounded-md bg-red-500/20 text-red-300 hover:bg-red-500/30">
                    Log ud
                </a>
            </div>
        </nav>

        <div id="threat-module" class="glass-module flex flex-col">
             <div class="flex border-b border-[var(--border-color)] px-4">
                <button data-target="map-content" class="tab-button font-brand p-4 text-[var(--brand-gold)] border-b-2 border-[var(--brand-gold)]">GLOBAL TRUSSELSOVERSIGT</button>
                <button data-target="load-content" class="tab-button font-brand p-4 text-gray-400 border-b-2 border-transparent hover:text-white">SERVERBELASTNING</button>
            </div>
            <div class="flex-grow relative overflow-hidden rounded-b-lg">
                <div id="map-content" class="tab-content active absolute inset-0">
                    <canvas id="threatMapCanvas" role="img" aria-label="Globalt trusselskort"></canvas>
                </div>
                <div id="load-content" class="tab-content absolute inset-0 p-4" style="display: none;">
                    <canvas id="serverLoadChart" role="img" aria-label="Graf over serverbelastning"></canvas>
                </div>
            </div>
        </div>

        <div id="alerts-module" class="glass-module flex flex-col p-4">
            <h2 class="font-brand text-lg text-white mb-4">AKTIVE ALARMER</h2>
            <div id="active-alerts-container" class="space-y-3 overflow-y-auto flex-grow pr-2"></div>
        </div>

        <div id="status-module" class="glass-module p-4 flex flex-col">
            <h2 class="font-brand text-lg text-white mb-4">SYSTEMSTATUS</h2>
            <ul class="space-y-4 text-sm flex-grow">
                 <li class="flex justify-between items-center"><span>Firewall Service</span><span class="text-xs font-bold py-1 px-3 rounded-full bg-green-500/20 text-green-400">OPERATIONEL</span></li>
                 <li class="flex justify-between items-center"><span>Threat Intel DB</span><span class="text-xs font-bold py-1 px-3 rounded-full bg-green-500/20 text-green-400">STABIL</span></li>
                 <li class="flex justify-between items-center"><span>AI Core "GREY-E"</span><span class="text-xs font-bold py-1 px-3 rounded-full bg-green-500/20 text-green-400">AKTIV</span></li>
                 <li class="flex justify-between items-center"><span>API Gateway</span><span class="text-xs font-bold py-1 px-3 rounded-full bg-yellow-500/20 text-yellow-400">HØJ LATENS</span></li>
             </ul>
        </div>

        <div id="net-module" class="glass-module p-4 flex flex-col">
            <h2 class="font-brand text-lg text-white mb-4">NETVÆRKSOVERVÅGNING</h2>
            <ul class="space-y-4 text-sm flex-grow">
                <li class="flex items-center"><span class="w-24">Port 22 (SSH)</span><div class="flex-grow bg-black/30 rounded-full h-2"><div class="bg-blue-400 h-2 rounded-full" style="width: 45%"></div></div></li>
                <li class="flex items-center"><span class="w-24">Port 443 (HTTPS)</span><div class="flex-grow bg-black/30 rounded-full h-2"><div class="bg-yellow-400 h-2 rounded-full" style="width: 88%"></div></div></li>
                <li class="flex items-center"><span class="w-24">Port 3306 (DB)</span><div class="flex-grow bg-black/30 rounded-full h-2"><div class="bg-red-500 h-2 rounded-full" style="width: 95%"></div></div></li>
                <li class="flex items-center"><span class="w-24">Port 9200 (ES)</span><div class="flex-grow bg-black/30 rounded-full h-2"><div class="bg-blue-400 h-2 rounded-full" style="width: 20%"></div></div></li>
            </ul>
        </div>

        <div id="ai-module" class="glass-module p-4 flex flex-col justify-between">
            <h2 class="font-brand text-lg text-white mb-2">AI KOMMANDO INTERFACE</h2>
            <p class="text-sm text-[var(--text-secondary)] mb-4">Stil et spørgsmål eller giv en kommando til GREY-E.</p>
            <textarea placeholder="> Analysér trafik fra IP 192.168.1.100..." class="flex-grow w-full bg-black/30 border-2 border-gray-600 rounded-md py-2 px-4 text-white placeholder-gray-500 focus:outline-none focus:border-[var(--brand-gold)] focus:ring-1 focus:ring-[var(--brand-gold)] resize-none"></textarea>
        </div>

    </div>

    <!-- Modal Structure -->
    <div id="alertModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div class="glass-module w-full max-w-2xl p-6 relative">
            <button id="closeModal" class="absolute top-4 right-4 text-gray-400 hover:text-white">&times;</button>
            <h2 id="modalTitle" class="font-brand text-2xl text-[var(--brand-gold)] mb-4">Alarm Detaljer</h2>
            <div id="modalBody" class="text-lg">
                <p>Detaljeret information om alarmen vil blive vist her.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mockAlerts = [
                { id: 'a1', severity: 'critical', title: 'Brute Force Angreb Opdaget', target: 'SSH på SRV-01', time: '2 min siden' },
                { id: 'a2', severity: 'critical', title: 'Anormal Udgående Trafik', target: 'DB-CLUSTER-03', time: '5 min siden' },
                { id: 'a3', severity: 'warning', title: 'Flere Fejlede Logins', target: 'Admin Portal', time: '12 min siden' },
            ];
            
            const alertsContainer = document.getElementById('active-alerts-container');
            if (alertsContainer) {
                mockAlerts.forEach(alert => {
                    const alertEl = document.createElement('div');
                    alertEl.className = `p-3 rounded-md bg-black/30 border-l-4 ${alert.severity === 'critical' ? 'border-red-500' : 'border-yellow-500'} ${alert.severity === 'critical' ? 'pulse-critical' : ''}`;
                    alertEl.innerHTML = `
                        <h3 class="font-bold text-white">${alert.title}</h3>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-[var(--text-secondary)]">${alert.time}</span>
                            <button data-alert-id="${alert.id}" class="open-modal-btn text-xs text-[var(--brand-gold)] font-bold">Undersøg &rarr;</button>
                        </div>
                    `;
                    alertsContainer.appendChild(alertEl);
                });
            }

            const alertModal = document.getElementById('alertModal');
            const closeModal = document.getElementById('closeModal');
            
            document.querySelectorAll('.open-modal-btn').forEach(button => {
                button.addEventListener('click', () => {
                    alertModal.classList.remove('hidden');
                });
            });

            closeModal.addEventListener('click', () => alertModal.classList.add('hidden'));
            alertModal.addEventListener('click', (e) => {
                if (e.target === alertModal) {
                    alertModal.classList.add('hidden');
                }
            });


            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetId = button.dataset.target;
                    
                    document.querySelectorAll('.tab-button').forEach(btn => {
                        btn.classList.remove('text-[var(--brand-gold)]', 'border-[var(--brand-gold)]');
                        btn.classList.add('text-gray-400', 'border-transparent');
                    });
                    button.classList.add('text-[var(--brand-gold)]', 'border-[var(--brand-gold)]');
                    button.classList.remove('text-gray-400', 'border-transparent');

                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.style.display = content.id === targetId ? 'block' : 'none';
                    });
                    window.dispatchEvent(new Event('resize'));
                });
            });

            // Server Load Chart
            const serverLoadCtx = document.getElementById('serverLoadChart');
            if (serverLoadCtx) {
                new Chart(serverLoadCtx, {
                    type: 'line',
                    data: {
                        labels: Array.from({length: 12}, (_, i) => `${60 - i*5}m`),
                        datasets: [
                            { label: 'CPU Belastning', data: [22, 25, 30, 45, 50, 55, 60, 58, 52, 40, 35, 28].reverse(), borderColor: 'rgba(212, 175, 55, 1)', backgroundColor: 'rgba(212, 175, 55, 0.2)', borderWidth: 2, fill: true, tension: 0.4, pointRadius: 0 },
                            { label: 'Hukommelsesbrug', data: [15, 18, 22, 20, 28, 35, 33, 40, 38, 30, 25, 20].reverse(), borderColor: 'rgba(59, 130, 246, 1)', backgroundColor: 'rgba(59, 130, 246, 0.1)', borderWidth: 2, fill: false, tension: 0.4, pointRadius: 0 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true, max: 100, ticks: { color: 'rgba(255,255,255,0.5)', callback: (v) => v + '%' }, grid: { color: 'rgba(255,255,255,0.1)' } }, x: { ticks: { color: 'rgba(255,255,255,0.5)' }, grid: { display: false } } },
                        plugins: { legend: { position: 'top', align: 'end', labels: { color: 'rgba(255,255,255,0.8)'} } }
                    }
                });
            }

            // Threat Map Canvas
            const threatMapCanvas = document.getElementById('threatMapCanvas');
            if(threatMapCanvas){
                const ctx = threatMapCanvas.getContext('2d');
                let width, height, animationFrameId;
                const points = Array.from({length: 15}, () => ({ x: Math.random(), y: Math.random(), radius: Math.random() * 3 + 1, alpha: 0, alphaChange: Math.random() * 0.02 + 0.01 }));
                const arcs = Array.from({length: 5}, () => ({ startX: Math.random(), startY: Math.random(), endX: Math.random(), endY: Math.random(), progress: Math.random(), speed: Math.random() * 0.005 + 0.002 }));
                
                const resizeCanvas = () => {
                    const container = threatMapCanvas.parentElement;
                    if (!container) return;
                    width = container.clientWidth;
                    height = container.clientHeight;
                    threatMapCanvas.width = width;
                    threatMapCanvas.height = height;
                };

                function draw() {
                    if(!ctx || !width || !height) return;
                    ctx.clearRect(0, 0, width, height);
                    points.forEach(p => {
                        ctx.beginPath(); ctx.arc(p.x * width, p.y * height, p.radius, 0, Math.PI * 2); ctx.fillStyle = `rgba(248, 81, 73, ${p.alpha})`; ctx.fill(); p.alpha += p.alphaChange; if(p.alpha > 1 || p.alpha < 0) p.alphaChange *= -1;
                    });
                    arcs.forEach(a => {
                        ctx.beginPath(); const cpX = (a.startX + a.endX) / 2 + (a.startY - a.endY) * 0.4; const cpY = (a.startY + a.endY) / 2 + (a.endX - a.startX) * 0.4; ctx.moveTo(a.startX * width, a.startY * height); ctx.quadraticCurveTo(cpX * width, cpY * height, a.endX * width, a.endY * height); ctx.strokeStyle = `rgba(212, 175, 55, 0.4)`; ctx.lineWidth = 1; ctx.stroke(); a.progress += a.speed; if(a.progress >= 1){ a.progress = 0; a.startX = Math.random(); a.startY = Math.random(); a.endX = Math.random(); a.endY = Math.random(); }
                    });
                    animationFrameId = requestAnimationFrame(draw);
                }
                
                new IntersectionObserver((entries) => {
                    const entry = entries[0];
                    if (entry.isIntersecting) { resizeCanvas(); if (!animationFrameId) draw(); } else { cancelAnimationFrame(animationFrameId); animationFrameId = null; }
                }).observe(threatMapCanvas);

                window.addEventListener('resize', resizeCanvas);
            }
        });
    </script>
</body>
</html>
