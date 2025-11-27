/**
 * Graphene Hero - 3D Hexagonal Mesh Network
 * BlackboxEYE × GreyEYE Fusion
 *
 * Reference: 3D hexagon lattice with golden spheres at vertices,
 * grey structural lines, and depth perspective like looking at
 * a tilted honeycomb surface.
 */

(function () {
    'use strict';

    const canvas = document.getElementById('graphene-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width, height;
    let time = 0;
    let mouseX = 0, mouseY = 0;
    let vertices = [];
    let edges = [];

    // Config matching reference images
    const cfg = {
        // Grid
        cols: 28,
        rows: 16,
        cellSize: 70,

        // 3D perspective
        tiltX: 0.5,        // Looking down at angle
        tiltY: 0.1,
        perspective: 1200,

        // Spheres
        sphereBaseSize: 12,
        sphereMinSize: 4,

        // Colors from reference
        gold: '#D4AF37',
        goldBright: '#F5D742',
        goldLight: '#FFE8A3',
        grey: '#6B7280',
        greyDark: '#374151',
        lineGrey: 'rgba(75, 85, 99, 0.35)',
        lineGold: 'rgba(212, 175, 55, 0.25)',

        // Animation
        waveSpeed: 0.008,
        waveHeight: 40,
        pulseSpeed: 0.02,

        reduceMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches
    };

    function init() {
        resize();
        createMesh();
        window.addEventListener('resize', debounce(resize, 200));
        canvas.addEventListener('mousemove', onMouseMove);

        if (!cfg.reduceMotion) {
            requestAnimationFrame(animate);
        } else {
            render();
        }
    }

    function resize() {
        const rect = canvas.parentElement?.getBoundingClientRect() || { width: window.innerWidth, height: window.innerHeight };
        width = rect.width;
        height = rect.height;

        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        createMesh();
    }

    function createMesh() {
        vertices = [];
        edges = [];

        const hexWidth = cfg.cellSize * 1.5;
        const hexHeight = cfg.cellSize * Math.sqrt(3);

        // Offset to center the grid
        const gridWidth = cfg.cols * hexWidth;
        const gridHeight = cfg.rows * hexHeight;
        const offsetX = (width - gridWidth) / 2 + hexWidth;
        const offsetY = (height - gridHeight) / 2 - 50;

        // Create hexagonal grid vertices
        for (let row = 0; row < cfg.rows; row++) {
            for (let col = 0; col < cfg.cols; col++) {
                // Hexagon center position
                const x = col * hexWidth + (row % 2) * (hexWidth / 2) + offsetX;
                const y = row * hexHeight * 0.75 + offsetY;

                // Add center vertex
                const centerIdx = vertices.length;
                vertices.push({
                    x: x,
                    y: y,
                    z: 0,
                    baseX: x,
                    baseY: y,
                    row: row,
                    col: col,
                    isGold: (row + col) % 4 === 0,
                    phase: (row * 0.3 + col * 0.2),
                    size: cfg.sphereBaseSize * (0.6 + Math.random() * 0.5)
                });

                // Create 6 hexagon corner vertices
                for (let i = 0; i < 6; i++) {
                    const angle = (Math.PI / 3) * i;
                    const vx = x + Math.cos(angle) * cfg.cellSize * 0.5;
                    const vy = y + Math.sin(angle) * cfg.cellSize * 0.5;

                    // Check if vertex already exists nearby
                    let existing = vertices.findIndex(v =>
                        Math.abs(v.baseX - vx) < 5 && Math.abs(v.baseY - vy) < 5
                    );

                    if (existing === -1) {
                        existing = vertices.length;
                        vertices.push({
                            x: vx,
                            y: vy,
                            z: 0,
                            baseX: vx,
                            baseY: vy,
                            row: row,
                            col: col,
                            isGold: Math.random() > 0.65,
                            phase: (row * 0.2 + col * 0.3 + i * 0.5),
                            size: cfg.sphereBaseSize * (0.4 + Math.random() * 0.4)
                        });
                    }

                    // Edge from center to corner
                    edges.push({ from: centerIdx, to: existing });
                }
            }
        }

        // Connect adjacent vertices
        for (let i = 0; i < vertices.length; i++) {
            for (let j = i + 1; j < vertices.length; j++) {
                const dx = vertices[i].baseX - vertices[j].baseX;
                const dy = vertices[i].baseY - vertices[j].baseY;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist > 5 && dist < cfg.cellSize * 0.7) {
                    // Avoid duplicate edges
                    const exists = edges.some(e =>
                        (e.from === i && e.to === j) || (e.from === j && e.to === i)
                    );
                    if (!exists) {
                        edges.push({ from: i, to: j });
                    }
                }
            }
        }
    }

    function project(v) {
        // Apply wave animation to Z
        const wave = Math.sin(time * cfg.waveSpeed + v.phase) * cfg.waveHeight;
        const z = wave;

        // Mouse influence
        const mx = (mouseX - width / 2) / width;
        const my = (mouseY - height / 2) / height;

        // 3D rotation
        const tiltX = cfg.tiltX + my * 0.2;
        const tiltY = cfg.tiltY + mx * 0.15;

        // Rotate around X axis
        const y1 = v.y - height / 2;
        const z1 = z;
        const cosX = Math.cos(tiltX);
        const sinX = Math.sin(tiltX);
        const y2 = y1 * cosX - z1 * sinX;
        const z2 = y1 * sinX + z1 * cosX;

        // Rotate around Y axis
        const x1 = v.x - width / 2;
        const cosY = Math.cos(tiltY);
        const sinY = Math.sin(tiltY);
        const x2 = x1 * cosY + z2 * sinY;
        const z3 = -x1 * sinY + z2 * cosY;

        // Perspective projection
        const scale = cfg.perspective / (cfg.perspective + z3);

        return {
            x: width / 2 + x2 * scale,
            y: height / 2 + y2 * scale,
            z: z3,
            scale: scale
        };
    }

    function drawSphere(x, y, radius, isGold, pulse) {
        if (radius < 2) return;

        const r = radius * (1 + pulse * 0.1);

        // Glow
        const glowRadius = r * 2.5;
        const glow = ctx.createRadialGradient(x, y, r * 0.5, x, y, glowRadius);
        if (isGold) {
            glow.addColorStop(0, 'rgba(245, 215, 66, 0.5)');
            glow.addColorStop(0.4, 'rgba(212, 175, 55, 0.2)');
            glow.addColorStop(1, 'rgba(212, 175, 55, 0)');
        } else {
            glow.addColorStop(0, 'rgba(156, 163, 175, 0.3)');
            glow.addColorStop(0.4, 'rgba(107, 114, 128, 0.1)');
            glow.addColorStop(1, 'rgba(107, 114, 128, 0)');
        }
        ctx.beginPath();
        ctx.arc(x, y, glowRadius, 0, Math.PI * 2);
        ctx.fillStyle = glow;
        ctx.fill();

        // Main sphere gradient
        const grad = ctx.createRadialGradient(
            x - r * 0.3, y - r * 0.3, r * 0.1,
            x, y, r
        );
        if (isGold) {
            grad.addColorStop(0, '#FFFEF0');
            grad.addColorStop(0.15, cfg.goldLight);
            grad.addColorStop(0.4, cfg.goldBright);
            grad.addColorStop(0.7, cfg.gold);
            grad.addColorStop(1, '#7A6420');
        } else {
            grad.addColorStop(0, '#F3F4F6');
            grad.addColorStop(0.3, '#D1D5DB');
            grad.addColorStop(0.6, cfg.grey);
            grad.addColorStop(1, cfg.greyDark);
        }

        ctx.beginPath();
        ctx.arc(x, y, r, 0, Math.PI * 2);
        ctx.fillStyle = grad;
        ctx.fill();

        // Highlight
        const hl = ctx.createRadialGradient(
            x - r * 0.35, y - r * 0.35, 0,
            x - r * 0.35, y - r * 0.35, r * 0.45
        );
        hl.addColorStop(0, 'rgba(255,255,255,0.9)');
        hl.addColorStop(0.5, 'rgba(255,255,255,0.3)');
        hl.addColorStop(1, 'rgba(255,255,255,0)');
        ctx.beginPath();
        ctx.arc(x - r * 0.35, y - r * 0.35, r * 0.45, 0, Math.PI * 2);
        ctx.fillStyle = hl;
        ctx.fill();
    }

    function render() {
        // Dark background
        ctx.fillStyle = '#080A0C';
        ctx.fillRect(0, 0, width, height);

        // Subtle radial gradient
        const bg = ctx.createRadialGradient(width/2, height/2, 0, width/2, height/2, Math.max(width, height) * 0.6);
        bg.addColorStop(0, 'rgba(20, 24, 28, 0.5)');
        bg.addColorStop(1, 'rgba(8, 10, 12, 0)');
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, height);

        // Project all vertices
        const projected = vertices.map(v => ({
            ...v,
            ...project(v)
        }));

        // Sort by Z for depth (back to front)
        const sorted = [...projected].sort((a, b) => a.z - b.z);

        // Draw edges first
        ctx.lineCap = 'round';
        for (const edge of edges) {
            const a = projected[edge.from];
            const b = projected[edge.to];

            // Calculate average depth for edge opacity
            const avgZ = (a.z + b.z) / 2;
            const depthFactor = Math.max(0.1, Math.min(1, (avgZ + 100) / 200));

            // Edge color based on connected vertices
            const isGoldEdge = a.isGold || b.isGold;
            const alpha = 0.15 + depthFactor * 0.2;

            ctx.strokeStyle = isGoldEdge
                ? `rgba(212, 175, 55, ${alpha})`
                : `rgba(75, 85, 99, ${alpha})`;
            ctx.lineWidth = 0.5 + depthFactor * 0.5;

            ctx.beginPath();
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(b.x, b.y);
            ctx.stroke();
        }

        // Draw spheres (back to front)
        for (const v of sorted) {
            const pulse = Math.sin(time * cfg.pulseSpeed + v.phase);
            const size = v.size * v.scale;

            // Only draw if visible and large enough
            if (size > cfg.sphereMinSize && v.x > -50 && v.x < width + 50 && v.y > -50 && v.y < height + 50) {
                drawSphere(v.x, v.y, size, v.isGold, pulse);
            }
        }
    }

    function animate() {
        time++;
        render();
        requestAnimationFrame(animate);
    }

    function onMouseMove(e) {
        const rect = canvas.getBoundingClientRect();
        mouseX = e.clientX - rect.left;
        mouseY = e.clientY - rect.top;
    }

    function debounce(fn, ms) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), ms);
        };
    }

    // Start
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
