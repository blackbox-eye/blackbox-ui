/**
 * Graphene Hero - 3D Hexagonal Mesh Network
 * BlackboxEYE × GreyEYE Fusion
 *
 * Matching reference: Dark metallic hexagon lattice with subtle
 * gold accent spheres at vertices. Grey/silver structural tubes.
 * Perspective view looking down at tilted surface.
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

    // Config - matching dark metallic reference
    const cfg = {
        // Grid density
        hexSize: 55,

        // 3D perspective
        perspective: 800,
        tiltX: 0.45,
        tiltY: 0.08,

        // Node sizes - MUCH smaller than before
        nodeRadius: 3,
        goldNodeRadius: 4,

        // Colors - subtle, metallic
        colors: {
            background: '#0A0C0E',
            tube: 'rgba(80, 90, 100, 0.6)',
            tubeHighlight: 'rgba(140, 150, 160, 0.4)',
            node: '#4A5568',
            nodeHighlight: '#718096',
            gold: '#C9A227',
            goldGlow: 'rgba(201, 162, 39, 0.3)',
            goldBright: '#E8D48B'
        },


        // Animation
        waveSpeed: 0.004,
        waveAmplitude: 25,

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

        // Calculate grid size to cover viewport plus margin
        const cols = Math.ceil(width / hexW) + 4;
        const rows = Math.ceil(height / (hexH * 0.75)) + 4;

        // Center offset
        const offsetX = -hexW * 2;
        const offsetY = -hexH;

        // Create hex centers
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

                // Round to avoid duplicates
                const key = `${Math.round(nx)},${Math.round(ny)}`;

                if (!nodeMap.has(key)) {
                    const isGold = Math.random() < 0.12; // 12% gold nodes - subtle
                    const node = {
                        x: nx,
                        y: ny,
                        baseX: nx,
                        baseY: ny,
                        z: 0,
                        isGold: isGold,
                        phase: (center.row * 0.4 + center.col * 0.3 + i * 0.2)
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
        // Wave animation on Z
        const wave = Math.sin(time * cfg.waveSpeed + node.phase) * cfg.waveAmplitude;
        const z = wave;

        // Mouse influence
        const mx = (mouseX - 0.5) * 0.15;
        const my = (mouseY - 0.5) * 0.12;

        const tiltX = cfg.tiltX + my;
        const tiltY = cfg.tiltY + mx;

        // Center point
        const cx = width / 2;
        const cy = height / 2;

        // Translate to origin
        let x = node.baseX - cx;
        let y = node.baseY - cy;

        // Rotate around X (tilt forward)
        const cosX = Math.cos(tiltX);
        const sinX = Math.sin(tiltX);
        const y1 = y * cosX - z * sinX;
        const z1 = y * sinX + z * cosX;

        // Rotate around Y (side tilt)
        const cosY = Math.cos(tiltY);
        const sinY = Math.sin(tiltY);
        const x1 = x * cosY + z1 * sinY;
        const z2 = -x * sinY + z1 * cosY;

        // Perspective projection
        const scale = cfg.perspective / (cfg.perspective + z2);

        return {
            x: cx + x1 * scale,
            y: cy + y1 * scale,
            z: z2,
            scale: scale
        };
    }

    function render() {
        // Clear with dark background
        ctx.fillStyle = cfg.colors.background;
        ctx.fillRect(0, 0, width, height);

        // Subtle gradient overlay
        const grad = ctx.createRadialGradient(
            width * 0.3, height * 0.3, 0,
            width * 0.5, height * 0.5, Math.max(width, height) * 0.8
        );
        grad.addColorStop(0, 'rgba(30, 35, 40, 0.4)');
        grad.addColorStop(0.5, 'rgba(15, 18, 22, 0.2)');
        grad.addColorStop(1, 'rgba(5, 6, 8, 0)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, width, height);

        // Project all nodes
        const projected = nodes.map((node, i) => ({
            ...node,
            idx: i,
            ...project3D(node)
        }));

        // Sort by Z (back to front)
        const sorted = [...projected].sort((a, b) => a.z - b.z);

        // Draw connections (metallic tubes) first
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        for (const conn of connections) {
            const a = projected[conn.from];
            const b = projected[conn.to];

            // Skip if out of view
            if (a.x < -50 || a.x > width + 50 || b.x < -50 || b.x > width + 50) continue;
            if (a.y < -50 || a.y > height + 50 || b.y < -50 || b.y > height + 50) continue;

            // Depth-based opacity
            const avgZ = (a.z + b.z) / 2;
            const depthFactor = Math.max(0.2, Math.min(1, (avgZ + 80) / 160));

            // Metallic tube effect
            const lineWidth = 1.5 * Math.min(a.scale, b.scale);

            // Draw tube shadow
            ctx.strokeStyle = `rgba(0, 0, 0, ${0.3 * depthFactor})`;
            ctx.lineWidth = lineWidth + 1;
            ctx.beginPath();
            ctx.moveTo(a.x + 1, a.y + 1);
            ctx.lineTo(b.x + 1, b.y + 1);
            ctx.stroke();

            // Draw main tube - grey metallic
            const tubeGrad = ctx.createLinearGradient(a.x, a.y, b.x, b.y);
            tubeGrad.addColorStop(0, `rgba(70, 80, 90, ${0.5 * depthFactor})`);
            tubeGrad.addColorStop(0.5, `rgba(100, 110, 120, ${0.6 * depthFactor})`);
            tubeGrad.addColorStop(1, `rgba(70, 80, 90, ${0.5 * depthFactor})`);

            ctx.strokeStyle = tubeGrad;
            ctx.lineWidth = lineWidth;
            ctx.beginPath();
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(b.x, b.y);
            ctx.stroke();

            // Tube highlight (top edge)
            ctx.strokeStyle = `rgba(150, 160, 170, ${0.2 * depthFactor})`;
            ctx.lineWidth = lineWidth * 0.3;
            ctx.beginPath();
            ctx.moveTo(a.x, a.y - lineWidth * 0.3);
            ctx.lineTo(b.x, b.y - lineWidth * 0.3);
            ctx.stroke();
        }

        // Draw nodes (small spheres) - back to front
        for (const node of sorted) {
            // Skip if out of view
            if (node.x < -20 || node.x > width + 20 || node.y < -20 || node.y > height + 20) continue;

            const baseRadius = node.isGold ? cfg.goldNodeRadius : cfg.nodeRadius;
            const radius = baseRadius * node.scale;

            if (radius < 1.5) continue;

            // Depth factor for brightness
            const depthFactor = Math.max(0.3, Math.min(1, (node.z + 60) / 120));

            if (node.isGold) {
                // Gold node - subtle glow only
                const glowSize = radius * 2.5;
                const glow = ctx.createRadialGradient(node.x, node.y, radius * 0.5, node.x, node.y, glowSize);
                glow.addColorStop(0, `rgba(201, 162, 39, ${0.25 * depthFactor})`);
                glow.addColorStop(0.5, `rgba(201, 162, 39, ${0.1 * depthFactor})`);
                glow.addColorStop(1, 'rgba(201, 162, 39, 0)');
                ctx.beginPath();
                ctx.arc(node.x, node.y, glowSize, 0, Math.PI * 2);
                ctx.fillStyle = glow;
                ctx.fill();

                // Gold sphere - small and subtle
                const goldGrad = ctx.createRadialGradient(
                    node.x - radius * 0.3, node.y - radius * 0.3, radius * 0.1,
                    node.x, node.y, radius
                );
                goldGrad.addColorStop(0, cfg.colors.goldBright);
                goldGrad.addColorStop(0.4, cfg.colors.gold);
                goldGrad.addColorStop(1, '#8B7020');

                ctx.beginPath();
                ctx.arc(node.x, node.y, radius, 0, Math.PI * 2);
                ctx.fillStyle = goldGrad;
                ctx.fill();
            } else {
                // Grey metallic node
                const greyGrad = ctx.createRadialGradient(
                    node.x - radius * 0.3, node.y - radius * 0.3, radius * 0.1,
                    node.x, node.y, radius
                );
                greyGrad.addColorStop(0, `rgba(160, 170, 180, ${depthFactor})`);
                greyGrad.addColorStop(0.5, `rgba(90, 100, 110, ${depthFactor})`);
                greyGrad.addColorStop(1, `rgba(50, 55, 65, ${depthFactor})`);

                ctx.beginPath();
                ctx.arc(node.x, node.y, radius, 0, Math.PI * 2);
                ctx.fillStyle = greyGrad;
                ctx.fill();
            }

            // Tiny highlight on all nodes
            ctx.beginPath();
            ctx.arc(node.x - radius * 0.25, node.y - radius * 0.25, radius * 0.35, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255, 255, 255, ${0.4 * depthFactor})`;
            ctx.fill();
        }

        // Top fade gradient (content blending)
        const topFade = ctx.createLinearGradient(0, 0, 0, height * 0.15);
        topFade.addColorStop(0, 'rgba(10, 12, 14, 0.6)');
        topFade.addColorStop(1, 'rgba(10, 12, 14, 0)');
        ctx.fillStyle = topFade;
        ctx.fillRect(0, 0, width, height * 0.15);
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

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
