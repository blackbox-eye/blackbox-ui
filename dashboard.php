<?php
// --- PHP SESSION & SIKKERHED ---
// Bevarer den eksisterende logik for at sikre, at kun autoriserede agenter har adgang.
session_start();

// Inkluderer databaseforbindelsen
require_once __DIR__. '/db.php'; 

// Hvis agenten ikke er logget ind, sendes de tilbage til login-siden.
if (!isset($_SESSION['agent_id'])) {
    header('Location: agent-login.php');
    exit;
}

// Henter agentens ID og admin-status fra sessionen til brug i dashboardet.
$agent_id = $_SESSION['agent_id'];
$is_admin = $_SESSION['is_admin']?? false;

$page_title = 'Agent Dashboard';
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title);?> - Blackbox EYE</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Chakra+Petch:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        :root {
            --bg-color: #101419;
            --primary-accent: #FFC700;
            --text-high-emphasis: #EAEAEA;
            --text-medium-emphasis: #9CA3AF;
            --glass-border: rgba(255, 255, 255, 0.15);
            --glass-bg: rgba(22, 28, 39, 0.75);
            --status-green: #22c55e;
            --status-yellow: #eab308;
            --status-red: #ef4444;
            --status-blue: #3b82f6;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-high-emphasis);
            font-family: 'Inter', sans-serif;
        }

        /* Grundlæggende "glas"-effekt for alle paneler */
       .glass-panel {
            background-color: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 1rem; /* 16px */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        /* Layout med CSS Grid */
       .dashboard-grid {
            display: grid;
            grid-template-columns: 240px 1fr 300px; /* Sidebar | Main Content | Right Widgets */
            grid-template-rows: 80px 1fr; /* Header | Content Area */
            grid-template-areas:
                "header header header"
                "sidebar main widgets";
            height: 100vh;
            gap: 1.5rem; /* 24px */
            padding: 1.5rem;
        }

        /* Tildeler områder til grid-elementer */
        #main-header { grid-area: header; }
        #main-sidebar { grid-area: sidebar; }
        #main-content { grid-area: main; overflow: hidden; display: flex; flex-direction: column; }
        #main-widgets { grid-area: widgets; overflow-y: auto; }

        /* Styling for aktivt menupunkt i sidebar */
       .sidebar-link.active {
            background-color: rgba(255, 199, 0, 0.1);
            color: var(--primary-accent);
            border-right: 3px solid var(--primary-accent);
        }

        /* Tilpasset scrollbar for et mere integreret look */
        #main-widgets::-webkit-scrollbar { width: 6px; }
        #main-widgets::-webkit-scrollbar-track { background: transparent; }
        #main-widgets::-webkit-scrollbar-thumb { background-color: var(--glass-border); border-radius: 6px; }

        /* Animation for "pulsende" elementer */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px var(--status-blue), 0 0 10px var(--status-blue); }
            50% { box-shadow: 0 0 15px var(--status-blue), 0 0 25px var(--status-blue); }
        }
       .pulsing-glow {
            animation: pulse-glow 2.5s infinite ease-in-out;
        }
    </style>
</head>
<body class="antialiased">

    <div class="dashboard-grid">
        <header id="main-header" class="glass-panel flex items-center justify-between px-6">
            <div class="flex items-center gap-4">
                <img src="assets/logo.png" alt="Logo" class="h-10 w-10">
                <h1 class="text-xl font-bold tracking-wider" style="font-family: 'Chakra Petch', sans-serif;">BLACKBOX EYE // CONTROL PANEL</h1>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <p class="text-sm font-semibold"><?php echo htmlspecialchars($agent_id);?></p>
                    <p class="text-xs text-green-400">Status: Active</p>
                </div>
                <button class="p-2 rounded-full hover:bg-white/10 transition">
                    <i data-feather="user" class="h-6 w-6"></i>
                </button>
            </div>
        </header>

        <nav id="main-sidebar" class="glass-panel p-4 flex flex-col">
            <div class="space-y-2 flex-grow">
                <a href="#" class="sidebar-link active flex items-center gap-4 px-4 py-3 rounded-lg transition-colors">
                    <i data-feather="layout"></i><span>Dashboard</span>
                </a>
                <a href="#" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-white/5 transition-colors">
                    <i data-feather="briefcase"></i><span>Missions</span>
                </a>
                <a href="#" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-white/5 transition-colors">
                    <i data-feather="users"></i><span>Analysts</span>
                </a>
                <a href="#" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-white/5 transition-colors">
                    <i data-feather="shield"></i><span>Stealth</span>
                </a>
                <a href="#" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-white/5 transition-colors">
                    <i data-feather="bar-chart-2"></i><span>Status</span>
                </a>
                <a href="#" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-white/5 transition-colors">
                    <i data-feather="tool"></i><span>Tools</span>
                </a>
            </div>
            <div class="space-y-2 border-t border-white/10 pt-4">
                <a href="settings.php" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-white/5 transition-colors">
                    <i data-feather="settings"></i><span>Settings</span>
                </a>
                <a href="logout.php" class="sidebar-link flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-white/5 transition-colors">
                    <i data-feather="log-out"></i><span>Log Out</span>
                </a>
            </div>
        </nav>

        <main id="main-content" class="glass-panel p-6 flex flex-col">
            <div id="world-map-container" class="flex-grow relative bg-black/20 rounded-lg overflow-hidden">
                <canvas id="map-canvas" class="absolute inset-0"></canvas>
                <div class="absolute top-4 left-4 text-sm">
                    <p class="font-bold">GLOBAL THREAT OVERVIEW</p>
                    <p class="text-gray-400">Live Data Feed: Active</p>
                </div>
            </div>
            <div class="mt-6">
                <label for="ai-command" class="text-sm font-semibold text-gray-300 mb-2 block">GREY E // AI COMMAND INTERFACE</label>
                <div class="relative">
                    <input type="text" id="ai-command" placeholder="Indtast kommando eller forespørgsel til GREY E..."
                           class="w-full bg-black/30 border border-blue-500/50 rounded-lg py-3 pl-4 pr-12 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400 pulsing-glow">
                    <button class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-white">
                        <i data-feather="arrow-right-circle"></i>
                    </button>
                </div>
            </div>
        </main>

        <aside id="main-widgets" class="space-y-6">
            <div class="glass-panel p-4">
                <h3 class="font-bold text-lg mb-3">SYSTEM STATUS</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center"><span>OSINT Gateway</span><span class="font-mono text-green-400">OPERATIONAL</span></div>
                    <div class="flex justify-between items-center"><span>Titan Protocol</span><span class="font-mono text-green-400">ACTIVE</span></div>
                    <div class="flex justify-between items-center"><span>Cloud C2 Link</span><span class="font-mono text-green-400">STABLE</span></div>
                    <div class="flex justify-between items-center"><span>GREY E AI Core</span><span class="font-mono text-yellow-400">STANDBY</span></div>
                </div>
            </div>
            <div class="glass-panel p-4">
                <h3 class="font-bold text-lg mb-3">ACTIVE ALERTS</h3>
                <div class="space-y-3 text-sm">
                    <div class="bg-red-500/20 border-l-4 border-red-500 p-2 rounded">
                        <p class="font-bold">CRITICAL: Unauth. Access</p>
                        <p class="text-xs text-gray-400">IP: 188.42.1.109 | Port: 22</p>
                    </div>
                    <div class="bg-yellow-500/20 border-l-4 border-yellow-500 p-2 rounded">
                        <p class="font-bold">WARNING: Brute-force attempt</p>
                        <p class="text-xs text-gray-400">Target: admin@blackbox.codes</p>
                    </div>
                </div>
            </div>
            <div class="glass-panel p-4">
                <h3 class="font-bold text-lg mb-3">NETWORK WATCHLIST</h3>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div><p class="font-mono text-2xl">22</p><p class="text-xs text-gray-400">SSH</p></div>
                    <div><p class="font-mono text-2xl">80</p><p class="text-xs text-gray-400">HTTP</p></div>
                    <div><p class="font-mono text-2xl">443</p><p class="text-xs text-gray-400">HTTPS</p></div>
                    <div><p class="font-mono text-2xl">3306</p><p class="text-xs text-gray-400">MySQL</p></div>
                    <div><p class="font-mono text-2xl">9200</p><p class="text-xs text-gray-400">Elastic</p></div>
                </div>
            </div>
        </aside>
    </div>

    <script>
        // Aktiverer Feather Icons
        feather.replace();

        // Simpel animation for verdenskortet
        const canvas = document.getElementById('map-canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            
            // Simulerer datapunkter
            const points =;

            function drawMap() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Tegner forbindelseslinjer
                ctx.strokeStyle = 'rgba(59, 130, 246, 0.3)';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(points.x * canvas.width, points.y * canvas.height);
                ctx.lineTo(points.[1]x * canvas.width, points.[1]y * canvas.height);
                ctx.lineTo(points.x * canvas.width, points.y * canvas.height);
                ctx.stroke();

                // Tegner datapunkter med pulserende effekt
                points.forEach(point => {
                    const x = point.x * canvas.width;
                    const y = point.y * canvas.height;
                    const radius = 5 + Math.sin(Date.now() * 0.002 + x) * 2;
                    
                    ctx.fillStyle = 'rgba(255, 199, 0, 0.8)';
                    ctx.beginPath();
                    ctx.arc(x, y, radius, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = 'white';
                    ctx.font = '10px Inter';
                    ctx.fillText(point.label, x + 10, y + 4);
                });

                requestAnimationFrame(drawMap);
            }
            drawMap();
        }
    </script>
</body>
</html>
