/**
 * Graphene Hero - 3D Hexagonal Mesh Network
 * BlackboxEYE × GreyEYE Fusion
 *
 * Reference: Dark metallic hexagon lattice with thick cylindrical tubes,
 * small dark metallic spheres at joints, warm golden ambient glow
 * in the background/horizon. Industrial carbon fiber aesthetic.
 */

(function () {
    'use strict';

    const canvas = document.getElementById('graphene-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width, height;
    let time = 0;
    let mouseX = 0.5, mouseY = 0.5;
    let nodes = [];
    let connections = [];

    // Config - matching reference: dark metallic with warm background glow
    const cfg = {
        // Grid - LARGER hexagons like reference
        hexSize: 85,

        // 3D perspective - looking down at surface
        perspective: 900,
        tiltX: 0.52,    // More tilt to match reference angle
        tiltY: 0.05,

        // Node sizes - small dark metallic joints
        nodeRadius: 5,

        // Tube thickness - MUCH thicker like reference
        tubeWidth: 3.5,

        // Colors - dark metallic, no gold nodes
        colors: {
            background: '#0A0B0D',
            // Tubes - dark grey metallic
            tubeBase: 'rgb(45, 50, 58)',
            tubeHighlight: 'rgb(90, 100, 115)',
            tubeShadow: 'rgb(20, 22, 28)',
            // Nodes - dark metallic spheres
            nodeBase: 'rgb(35, 40, 48)',
            nodeHighlight: 'rgb(80, 90, 105)',
            nodeShadow: 'rgb(15, 18, 22)',
            // Warm ambient glow for background
            warmGlow: 'rgb(180, 130, 60)',
            warmGlowLight: 'rgb(220, 170, 90)'
        },

        // Animation - subtle wave
        waveSpeed: 0.003,
        waveAmplitude: 20,

        reduceMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches
    };

    function init() {
        resize();
        buildHexGrid();

        window.addEventListener('resize', debounce(resize, 200));
        canvas.addEventListener('mousemove', onMouseMove);
        canvas.addEventListener('mouseleave', () => { mouseX = 0.5; mouseY = 0.5; });

        if (!cfg.reduceMotion) {
            requestAnimationFrame(animate);
        } else {
            render();
        }
    }

    function resize() {
        const container = canvas.parentElement;
        const rect = container ? container.getBoundingClientRect() : { width: window.innerWidth, height: window.innerHeight };

        width = rect.width;
        height = rect.height;

        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        buildHexGrid();
    }

    function buildHexGrid() {
        nodes = [];
        connections = [];

        const hexW = cfg.hexSize * 1.5;
        const hexH = cfg.hexSize * Math.sqrt(3);

        // Grid to cover viewport plus margin
        const cols = Math.ceil(width / hexW) + 6;
        const rows = Math.ceil(height / (hexH * 0.75)) + 6;

        const offsetX = -hexW * 3;
        const offsetY = -hexH * 2;

        const centers = [];
        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < cols; col++) {
                const x = col * hexW + (row % 2) * (hexW / 2) + offsetX;
                const y = row * hexH * 0.75 + offsetY;
                centers.push({ x, y, row, col });
            }
        }

        // Create nodes at hex vertices
        const nodeMap = new Map();

        for (const center of centers) {
            const corners = [];

            for (let i = 0; i < 6; i++) {
                const angle = (Math.PI / 3) * i - Math.PI / 6;
                const nx = center.x + Math.cos(angle) * cfg.hexSize * 0.58;
                const ny = center.y + Math.sin(angle) * cfg.hexSize * 0.58;

                const key = `${Math.round(nx)},${Math.round(ny)}`;

                if (!nodeMap.has(key)) {
                    const node = {
                        x: nx,
                        y: ny,
                        baseX: nx,
                        baseY: ny,
                        z: 0,
                        phase: (center.row * 0.3 + center.col * 0.25 + i * 0.15)
                    };
                    nodeMap.set(key, nodes.length);
                    nodes.push(node);
                }
                corners.push(nodeMap.get(key));
            }

            // Connect hex edges
            for (let i = 0; i < 6; i++) {
                const from = corners[i];
                const to = corners[(i + 1) % 6];
                if (from !== undefined && to !== undefined) {
                    const connKey = from < to ? `${from}-${to}` : `${to}-${from}`;
                    if (!connections.some(c => {
                        const k = c.from < c.to ? `${c.from}-${c.to}` : `${c.to}-${c.from}`;
                        return k === connKey;
                    })) {
                        connections.push({ from, to });
                    }
                }
            }
        }
    }

    function project3D(node) {
        const wave = Math.sin(time * cfg.waveSpeed + node.phase) * cfg.waveAmplitude;
        const z = wave;

        const mx = (mouseX - 0.5) * 0.1;
        const my = (mouseY - 0.5) * 0.08;

        const tiltX = cfg.tiltX + my;
        const tiltY = cfg.tiltY + mx;

        const cx = width / 2;
        const cy = height / 2;

        let x = node.baseX - cx;
        let y = node.baseY - cy;

        const cosX = Math.cos(tiltX);
        const sinX = Math.sin(tiltX);
        const y1 = y * cosX - z * sinX;
        const z1 = y * sinX + z * cosX;

        const cosY = Math.cos(tiltY);
        const sinY = Math.sin(tiltY);
        const x1 = x * cosY + z1 * sinY;
        const z2 = -x * sinY + z1 * cosY;

        const scale = cfg.perspective / (cfg.perspective + z2);

        return {
            x: cx + x1 * scale,
            y: cy + y1 * scale,
            z: z2,
            scale: scale
        };
    }

    function render() {
        // Dark background
        ctx.fillStyle = cfg.colors.background;
        ctx.fillRect(0, 0, width, height);

        // Warm ambient glow in the distance (top/back of scene)
        // This creates the golden/copper reflection seen in reference
        const warmGlow = ctx.createRadialGradient(
            width * 0.5, height * 0.15, 0,
            width * 0.5, height * 0.3, height * 0.7
        );
        warmGlow.addColorStop(0, 'rgba(200, 150, 70, 0.25)');
        warmGlow.addColorStop(0.3, 'rgba(180, 120, 50, 0.15)');
        warmGlow.addColorStop(0.6, 'rgba(100, 70, 30, 0.08)');
        warmGlow.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = warmGlow;
        ctx.fillRect(0, 0, width, height);

        // Project all nodes
        const projected = nodes.map((node, i) => ({
            ...node,
            idx: i,
            ...project3D(node)
        }));

        // Sort connections by average Z (back to front)
        const sortedConnections = [...connections].map(c => {
            const a = projected[c.from];
            const b = projected[c.to];
            return { ...c, avgZ: (a.z + b.z) / 2, a, b };
        }).sort((a, b) => a.avgZ - b.avgZ);

        // Draw tubes (thick metallic cylinders)
        for (const conn of sortedConnections) {
            const { a, b, avgZ } = conn;

            if (a.x < -100 || a.x > width + 100 || b.x < -100 || b.x > width + 100) continue;
            if (a.y < -100 || a.y > height + 100 || b.y < -100 || b.y > height + 100) continue;

            // Depth factor for visibility
            const depthFactor = Math.max(0.15, Math.min(1, (avgZ + 100) / 200));
            
            // Distance-based blur effect (fog in background)
            const fogFactor = Math.max(0, Math.min(1, (avgZ + 50) / 150));
            
            const baseWidth = cfg.tubeWidth * Math.min(a.scale, b.scale);
            const tubeWidth = baseWidth * (0.7 + fogFactor * 0.3);

            // Calculate perpendicular for 3D tube effect
            const dx = b.x - a.x;
            const dy = b.y - a.y;
            const len = Math.sqrt(dx * dx + dy * dy);
            if (len < 1) continue;
            
            const nx = -dy / len;
            const ny = dx / len;

            // Draw shadow first
            ctx.strokeStyle = `rgba(0, 0, 0, ${0.5 * depthFactor})`;
            ctx.lineWidth = tubeWidth + 2;
            ctx.lineCap = 'round';
            ctx.beginPath();
            ctx.moveTo(a.x + 1.5, a.y + 1.5);
            ctx.lineTo(b.x + 1.5, b.y + 1.5);
            ctx.stroke();

            // Main tube body - dark metallic grey
            const tubeGrad = ctx.createLinearGradient(
                a.x + nx * tubeWidth, a.y + ny * tubeWidth,
                a.x - nx * tubeWidth, a.y - ny * tubeWidth
            );
            const r = Math.round(45 * depthFactor);
            const g = Math.round(52 * depthFactor);
            const bl = Math.round(62 * depthFactor);
            const rH = Math.round(95 * depthFactor);
            const gH = Math.round(108 * depthFactor);
            const bH = Math.round(125 * depthFactor);
            
            tubeGrad.addColorStop(0, `rgb(${r - 15}, ${g - 15}, ${bl - 15})`);
            tubeGrad.addColorStop(0.3, `rgb(${rH}, ${gH}, ${bH})`);
            tubeGrad.addColorStop(0.5, `rgb(${r + 10}, ${g + 10}, ${bl + 10})`);
            tubeGrad.addColorStop(0.7, `rgb(${r - 5}, ${g - 5}, ${bl - 5})`);
            tubeGrad.addColorStop(1, `rgb(${r - 20}, ${g - 20}, ${bl - 20})`);

            ctx.strokeStyle = tubeGrad;
            ctx.lineWidth = tubeWidth;
            ctx.beginPath();
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(b.x, b.y);
            ctx.stroke();

            // Specular highlight on top edge
            ctx.strokeStyle = `rgba(140, 155, 175, ${0.35 * depthFactor})`;
            ctx.lineWidth = tubeWidth * 0.25;
            ctx.beginPath();
            ctx.moveTo(a.x + nx * tubeWidth * 0.35, a.y + ny * tubeWidth * 0.35);
            ctx.lineTo(b.x + nx * tubeWidth * 0.35, b.y + ny * tubeWidth * 0.35);
            ctx.stroke();
        }

        // Sort nodes by Z for proper depth
        const sortedNodes = [...projected].sort((a, b) => a.z - b.z);

        // Draw nodes (small dark metallic spheres)
        for (const node of sortedNodes) {
            if (node.x < -30 || node.x > width + 30 || node.y < -30 || node.y > height + 30) continue;

            const radius = cfg.nodeRadius * node.scale;
            if (radius < 2) continue;

            const depthFactor = Math.max(0.2, Math.min(1, (node.z + 80) / 160));

            // Sphere shadow
            ctx.beginPath();
            ctx.arc(node.x + 1.5, node.y + 1.5, radius * 1.1, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(0, 0, 0, ${0.4 * depthFactor})`;
            ctx.fill();

            // Main sphere - dark metallic
            const sphereGrad = ctx.createRadialGradient(
                node.x - radius * 0.35, node.y - radius * 0.35, radius * 0.1,
                node.x, node.y, radius
            );
            
            const baseR = Math.round(50 * depthFactor);
            const baseG = Math.round(58 * depthFactor);
            const baseB = Math.round(70 * depthFactor);
            const hiR = Math.round(110 * depthFactor);
            const hiG = Math.round(125 * depthFactor);
            const hiB = Math.round(145 * depthFactor);

            sphereGrad.addColorStop(0, `rgb(${hiR + 30}, ${hiG + 30}, ${hiB + 30})`);
            sphereGrad.addColorStop(0.3, `rgb(${hiR}, ${hiG}, ${hiB})`);
            sphereGrad.addColorStop(0.6, `rgb(${baseR}, ${baseG}, ${baseB})`);
            sphereGrad.addColorStop(1, `rgb(${baseR - 20}, ${baseG - 20}, ${baseB - 20})`);

            ctx.beginPath();
            ctx.arc(node.x, node.y, radius, 0, Math.PI * 2);
            ctx.fillStyle = sphereGrad;
            ctx.fill();

            // Specular highlight
            ctx.beginPath();
            ctx.arc(node.x - radius * 0.3, node.y - radius * 0.3, radius * 0.4, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(180, 195, 215, ${0.5 * depthFactor})`;
            ctx.fill();
        }

        // Depth fog effect - darker at bottom (foreground)
        const fogBottom = ctx.createLinearGradient(0, height * 0.7, 0, height);
        fogBottom.addColorStop(0, 'rgba(10, 11, 13, 0)');
        fogBottom.addColorStop(1, 'rgba(10, 11, 13, 0.4)');
        ctx.fillStyle = fogBottom;
        ctx.fillRect(0, height * 0.7, width, height * 0.3);

        // Subtle vignette
        const vignette = ctx.createRadialGradient(
            width / 2, height / 2, height * 0.3,
            width / 2, height / 2, Math.max(width, height) * 0.8
        );
        vignette.addColorStop(0, 'rgba(0, 0, 0, 0)');
        vignette.addColorStop(1, 'rgba(0, 0, 0, 0.3)');
        ctx.fillStyle = vignette;
        ctx.fillRect(0, 0, width, height);
    }

    function animate() {
        time++;
        render();
        requestAnimationFrame(animate);
    }

    function onMouseMove(e) {
        const rect = canvas.getBoundingClientRect();
        mouseX = (e.clientX - rect.left) / rect.width;
        mouseY = (e.clientY - rect.top) / rect.height;
    }

    function debounce(fn, ms) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), ms);
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
