<?php
// --- PHP LOGIN LOGIK (FINAL VERSION – PLAIN PASSWORD VERSION) ---

session_start();

// Inkluderer databaseforbindelsen
// VIGTIGT: Sørg for at stien til db.php er korrekt i forhold til din filstruktur.
require_once __DIR__ . '/db.php'; 

$page_title = 'Agent Login';
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_id = trim($_POST['agent_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $pin      = trim($_POST['pin'] ?? '');
    $token    = trim($_POST['token'] ?? '');

    // Robusthedstjek: Sikrer at $pdo er tilgængelig før brug
    if (isset($pdo)) {
        // Forbereder og udfører databasekald for at finde agenten
        $stmt = $pdo->prepare("SELECT * FROM agents WHERE agent_id = ?");
        $stmt->execute([$agent_id]);
        $agent = $stmt->fetch();

        // Validerer login-oplysningerne (UDEN hash – ren tekst sammenligning)
        if ($agent && $password === $agent['password'] && $pin === $agent['pin']) {
            $_SESSION['agent_id'] = $agent['agent_id'];
            $_SESSION['is_admin'] = (bool)$agent['is_admin'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Ugyldigt Agent ID, Password eller PIN.";
        }
    } else {
        $error = "Databaseforbindelse kunne ikke etableres.";
    }
}
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Blackbox EYE</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Chakra+Petch:wght@700&display=swap" rel="stylesheet">

    <!-- --- ENDELIG, KORRIGERET STYLING --- -->
    <style>
        :root {
            --bg-color: #101419;
            --primary-accent: #FFC700;
            --text-high-emphasis: #EAEAEA;
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-bg: rgba(22, 28, 39, 0.75);
            /* OPDATERET: Øget gennemsigtighed med ca. 15% */
            --input-bg: rgba(25, 31, 41, 0.35); 
            --digital-rain-color: #008000;
            --digital-rain-gold: #FFD700;
            --digital-rain-white: #EAEAEA;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-high-emphasis);
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        .login-box-container {
            /* KORRIGERET: Animationen er nu korrekt og boksen forbliver synlig */
            animation: fade-in-box 0.8s 0.2s ease-out forwards;
            opacity: 0;
            will-change: opacity, transform;
        }

        .glass-effect {
            background-color: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }
        
        @keyframes fade-in-box {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* NYT: Subtil glitch-effekt til logo */
        .subtle-glitch-logo {
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            position: relative;
            /* OPDATERET: 15% mindre font-størrelse */
            font-size: 1.9rem; 
            line-height: 1;
            color: var(--text-high-emphasis);
            animation: glitch-subtle 4s infinite step-end;
        }

        .subtle-glitch-logo span {
            position: absolute;
            top: 0;
            left: 0;
        }

        .subtle-glitch-logo span:first-child {
            animation: glitch-subtle 4.2s infinite step-end;
            clip-path: polygon(0 0, 100% 0, 100% 45%, 0 45%);
            transform: translate(-0.04em, -0.02em);
            opacity: 0.9;
        }

        .subtle-glitch-logo span:last-child {
            animation: glitch-subtle 3.8s infinite step-end;
            clip-path: polygon(0 60%, 100% 60%, 100% 100%, 0 100%);
            transform: translate(0.04em, 0.02em);
            opacity: 0.9;
        }

        @keyframes glitch-subtle {
            0% { text-shadow: 0.01em 0 0 var(--digital-rain-color); }
            2% { text-shadow: 0.01em 0 0 var(--digital-rain-gold); }
            3% { text-shadow: -0.01em 0 0 var(--digital-rain-white); }
            4% { text-shadow: 0.01em 0 0 var(--digital-rain-color); }
            100% { text-shadow: 0.01em 0 0 var(--digital-rain-color); }
        }
        
        .custom-input {
            background-color: var(--input-bg) !important;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            border: 1px solid var(--glass-border) !important;
            color: var(--text-high-emphasis) !important;
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .custom-input:focus {
            outline: none;
            border-color: var(--primary-accent) !important;
            box-shadow: 0 0 0 2px rgba(255, 199, 0, 0.4);
        }

        .custom-input::placeholder {
            color: #9CA3AF;
            opacity: 1;
        }
    </style>
</head>
<body class="antialiased">

    <canvas id="login-canvas" class="absolute top-0 left-0 w-full h-full z-0"></canvas>

    <main class="relative min-h-screen flex items-center justify-center p-4">
        
        <div class="login-box-container w-full max-w-md">
            <div class="p-8 space-y-6 glass-effect rounded-2xl z-10">
                
                <header class="flex flex-col items-center text-center">
                    <img src="assets/logo.png" alt="Blackbox EYE Emblem" class="h-24 w-24 mb-4">
                    <!-- OPDATERET: Bruger den nye subtile glitch-effekt -->
                    <h1 class="subtle-glitch-logo" aria-label="Blackbox EYE">
                        Blackbox EYE&trade;
                        <span aria-hidden="true">Blackbox EYE&trade;</span>
                        <span aria-hidden="true">Blackbox EYE&trade;</span>
                    </h1>
                </header>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded-md text-center text-sm">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="agent-login.php" method="post" class="space-y-5">
                    <div>
                        <label for="agent_id" class="sr-only">Agent ID</label>
                        <input type="text" name="agent_id" id="agent_id" placeholder="Agent ID" required class="custom-input">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input type="password" name="password" id="password" placeholder="Password" required class="custom-input">
                    </div>
                    <div>
                        <label for="pin" class="sr-only">PIN</label>
                        <input type="password" name="pin" id="pin" placeholder="PIN" required class="custom-input">
                    </div>
                    <div>
                        <label for="token" class="sr-only">Token (valgfri)</label>
                        <input type="text" name="token" id="token" placeholder="Token (valgfri)" class="custom-input">
                    </div>
                    
                    <button type="submit" 
                            class="w-full font-bold py-3 px-4 rounded-md bg-amber-400 text-black hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-amber-500 transition-all duration-300 transform hover:scale-105">
                        AUTENTIFICER
                    </button>
                </form>

                <footer class="text-xs text-gray-500 text-center pt-2">
                    Adgang kræver autoriseret hardware-nøgle. Alle forsøg logges.
                </footer>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('login-canvas');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                
                const setupCanvas = () => {
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                };
                setupCanvas();

                let columns = Math.floor(canvas.width / 20);
                const drops = Array(columns).fill(1).map(() => Math.ceil(Math.random() * canvas.height));
                const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890@#$%^&*()_+-=[]{}|;':,./<>?".split('');
                
                const rainColor  = getComputedStyle(document.documentElement).getPropertyValue('--digital-rain-color').trim();
                const goldColor  = getComputedStyle(document.documentElement).getPropertyValue('--digital-rain-gold').trim();
                const whiteColor = getComputedStyle(document.documentElement).getPropertyValue('--digital-rain-white').trim();

                function drawDigitalRain() {
                    ctx.fillStyle = "rgba(16, 20, 25, 0.05)";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.font = "16px monospace";

                    for (let i = 0; i < drops.length; i++) {
                        const text = chars[Math.floor(Math.random() * chars.length)];
                        
                        const random = Math.random();
                        if (random > 0.98) {
                            ctx.fillStyle = goldColor;
                        } else if (random > 0.96) {
                             ctx.fillStyle = whiteColor;
                        } else {
                            ctx.fillStyle = rainColor;
                        }

                        ctx.fillText(text, i * 20, drops[i] * 20);

                        if (drops[i] * 20 > canvas.height && Math.random() > 0.975) {
                            drops[i] = 0;
                        }
                        drops[i]++;
                    }
                }
                
                let animationInterval = setInterval(drawDigitalRain, 35);

                window.addEventListener('resize', () => {
                    setupCanvas();
                    columns = Math.floor(canvas.width / 20);
                    for (let i = 0; i < columns; i++) {
                        drops[i] = Math.floor(Math.random() * (canvas.height / 20));
                    }
                });
            }
        });
    </script>
</body>
</html>
