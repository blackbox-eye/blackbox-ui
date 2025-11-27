/**
 * Graphene Hero - Photorealistic 3D Hexagonal Mesh
 * STRICT VISUAL OVERHAUL - Matching Reference Image
 * 
 * Design Principles:
 * - CYLINDRICAL TUBES (not lines) with chrome/silver material
 * - DARK METALLIC JOINTS (NO yellow dots!) with specular highlights
 * - WARM AMBIENT GLOW from below (amber/orange horizon)
 * - AGGRESSIVE DEPTH OF FIELD (blur/fade in background)
 * - Light source: Top-left
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

    // ============================================
    // CONFIGURATION - Strict Reference Match
    // ============================================
    const cfg = {
        // GEOMETRY: Large hexagons - zoomed in feel
        hexSize: 120,
        
        // PERSPECTIVE: Looking down at ~50° angle
        perspective: 650,
        tiltX: 0.58,
        tiltY: 0.04,
        
        // TUBE DIMENSIONS: THICK cylindrical tubes (4-6px foreground)
        tubeWidthForeground: 5.5,
        tubeWidthBackground: 1.8,
        
        // JOINT DIMENSIONS: Dark metallic balls
        jointRadiusForeground: 8,
        jointRadiusBackground: 3,
        
        // MATERIALS: Chrome/Silver + Dark Metal (NO GOLD/YELLOW)
        materials: {
            // Chrome tube gradient
            tubeHighlight: [160, 175, 195],   // Silver highlight
            tubeMid: [55, 65, 80],            // Chrome mid
            tubeShadow: [22, 28, 38],         // Dark shadow
            tubeEdge: [12, 15, 22],           // Very dark edge
            
            // Dark metallic joints (NO GOLD)
            jointHighlight: [90, 100, 120],   // Cool specular
            jointMid: [35, 42, 55],           // Body
            jointShadow: [15, 18, 25],        // Dark side
        },
        
        // DEPTH OF FIELD
        dofNear: -40,
        dofFar: 180,
        
        // ANIMATION
        waveSpeed: 0.0018,
        waveAmplitude: 18,
        
        reduceMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches
    };

    // ============================================
    // INITIALIZATION
    // ============================================
    function init() {
        resize();
        buildMesh();
        
        window.addEventListener('resize', debounce(resize, 150));
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
        
        buildMesh();
    }

    // ============================================
    // MESH GENERATION
    // ============================================
    function buildMesh() {
        nodes = [];
        connections = [];
        
        const hexW = cfg.hexSize * 1.5;
        const hexH = cfg.hexSize * Math.sqrt(3);
        
        const cols = Math.ceil(width / hexW) + 10;
        const rows = Math.ceil(height / (hexH * 0.75)) + 10;
        
        const offsetX = -hexW * 5;
        const offsetY = -hexH * 4;
        
        const nodeMap = new Map();
        
        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < cols; col++) {
                const cx = col * hexW + (row % 2) * (hexW / 2) + offsetX;
                const cy = row * hexH * 0.75 + offsetY;
                
                const corners = [];
                
                for (let i = 0; i < 6; i++) {
                    const angle = (Math.PI / 3) * i - Math.PI / 6;
                    const nx = cx + Math.cos(angle) * cfg.hexSize * 0.58;
                    const ny = cy + Math.sin(angle) * cfg.hexSize * 0.58;
                    
                    const key = `${Math.round(nx / 4)},${Math.round(ny / 4)}`;
                    
                    if (!nodeMap.has(key)) {
                        nodeMap.set(key, nodes.length);
                        nodes.push({
                            baseX: nx,
                            baseY: ny,
                            phase: row * 0.22 + col * 0.18 + i * 0.08
                        });
                    }
                    corners.push(nodeMap.get(key));
                }
                
                for (let i = 0; i < 6; i++) {
                    const from = corners[i];
                    const to = corners[(i + 1) % 6];
                    const connKey = from < to ? `${from}-${to}` : `${to}-${from}`;
                    if (!connections.find(c => c.key === connKey)) {
                        connections.push({ from, to, key: connKey });
                    }
                }
            }
        }
    }

    // ============================================
    // 3D PROJECTION
    // ============================================
    function project(node) {
        const wave = Math.sin(time * cfg.waveSpeed + node.phase) * cfg.waveAmplitude;
        const z = wave;
        
        const mx = (mouseX - 0.5) * 0.06;
        const my = (mouseY - 0.5) * 0.04;
        
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

    // ============================================
    // DEPTH OF FIELD CALCULATOR
    // ============================================
    function getDepthFactor(z) {
        const normalized = (z - cfg.dofNear) / (cfg.dofFar - cfg.dofNear);
        return 1 - Math.max(0, Math.min(1, normalized));
    }

    // ============================================
    // CYLINDER TUBE RENDERER
    // Draws a fake 3D cylinder - NOT a line!
    // ============================================
    function drawCylinder(ax, ay, bx, by, depthFactor) {
        const dx = bx - ax;
        const dy = by - ay;
        const len = Math.sqrt(dx * dx + dy * dy);
        if (len < 3) return;
        
        // Perpendicular for tube width
        const nx = -dy / len;
        const ny = dx / len;
        
        // Width interpolated by depth
        const tubeW = cfg.tubeWidthBackground + (cfg.tubeWidthForeground - cfg.tubeWidthBackground) * depthFactor;
        
        // Alpha/opacity for DOF fade
        const alpha = 0.25 + depthFactor * 0.75;
        
        const m = cfg.materials;
        
        // ===================
        // LAYER 1: Shadow underneath
        // ===================
        ctx.beginPath();
        ctx.moveTo(ax + 2, ay + 2);
        ctx.lineTo(bx + 2, by + 2);
        ctx.strokeStyle = `rgba(5, 8, 15, ${alpha * 0.6})`;
        ctx.lineWidth = tubeW + 2;
        ctx.lineCap = 'round';
        ctx.stroke();
        
        // ===================
        // LAYER 2: Tube body with cylindrical gradient
        // Gradient perpendicular to tube direction
        // ===================
        const gradient = ctx.createLinearGradient(
            ax + nx * tubeW, ay + ny * tubeW,
            ax - nx * tubeW, ay - ny * tubeW
        );
        
        const f = depthFactor;
        
        // Cylindrical shading: edge -> shadow -> highlight -> mid -> shadow -> edge
        gradient.addColorStop(0, `rgba(${m.tubeEdge[0]}, ${m.tubeEdge[1]}, ${m.tubeEdge[2]}, ${alpha})`);
        gradient.addColorStop(0.1, `rgba(${m.tubeShadow[0] * f}, ${m.tubeShadow[1] * f}, ${m.tubeShadow[2] * f}, ${alpha})`);
        gradient.addColorStop(0.35, `rgba(${m.tubeHighlight[0] * f}, ${m.tubeHighlight[1] * f}, ${m.tubeHighlight[2] * f}, ${alpha})`);
        gradient.addColorStop(0.5, `rgba(${m.tubeMid[0] * f}, ${m.tubeMid[1] * f}, ${m.tubeMid[2] * f}, ${alpha})`);
        gradient.addColorStop(0.65, `rgba(${m.tubeShadow[0] * f}, ${m.tubeShadow[1] * f}, ${m.tubeShadow[2] * f}, ${alpha})`);
        gradient.addColorStop(0.9, `rgba(${m.tubeShadow[0] * f * 0.5}, ${m.tubeShadow[1] * f * 0.5}, ${m.tubeShadow[2] * f * 0.5}, ${alpha})`);
        gradient.addColorStop(1, `rgba(${m.tubeEdge[0]}, ${m.tubeEdge[1]}, ${m.tubeEdge[2]}, ${alpha})`);
        
        ctx.beginPath();
        ctx.moveTo(ax, ay);
        ctx.lineTo(bx, by);
        ctx.strokeStyle = gradient;
        ctx.lineWidth = tubeW;
        ctx.lineCap = 'round';
        ctx.stroke();
        
        // ===================
        // LAYER 3: Specular highlight (top edge, light from top-left)
        // ===================
        if (depthFactor > 0.3) {
            ctx.beginPath();
            ctx.moveTo(ax + nx * tubeW * 0.4, ay + ny * tubeW * 0.4);
            ctx.lineTo(bx + nx * tubeW * 0.4, by + ny * tubeW * 0.4);
            ctx.strokeStyle = `rgba(200, 210, 225, ${alpha * 0.3 * f})`;
            ctx.lineWidth = tubeW * 0.15;
            ctx.lineCap = 'round';
            ctx.stroke();
        }
    }

    // ============================================
    // METALLIC JOINT RENDERER
    // Dark metallic sphere - NO YELLOW/GOLD
    // ============================================
    function drawJoint(x, y, depthFactor) {
        const radius = cfg.jointRadiusBackground + (cfg.jointRadiusForeground - cfg.jointRadiusBackground) * depthFactor;
        if (radius < 2) return;
        
        const alpha = 0.35 + depthFactor * 0.65;
        const m = cfg.materials;
        const f = depthFactor;
        
        // ===================
        // LAYER 1: Shadow
        // ===================
        ctx.beginPath();
        ctx.arc(x + 2.5, y + 2.5, radius * 1.15, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(3, 5, 10, ${alpha * 0.5})`;
        ctx.fill();
        
        // ===================
        // LAYER 2: Main sphere (dark metallic gradient)
        // Light from top-left
        // ===================
        const sphereGrad = ctx.createRadialGradient(
            x - radius * 0.4, y - radius * 0.4, radius * 0.05,
            x + radius * 0.15, y + radius * 0.15, radius * 1.1
        );
        
        sphereGrad.addColorStop(0, `rgba(${m.jointHighlight[0] + 50 * f}, ${m.jointHighlight[1] + 50 * f}, ${m.jointHighlight[2] + 50 * f}, ${alpha})`);
        sphereGrad.addColorStop(0.25, `rgba(${m.jointHighlight[0] * f}, ${m.jointHighlight[1] * f}, ${m.jointHighlight[2] * f}, ${alpha})`);
        sphereGrad.addColorStop(0.5, `rgba(${m.jointMid[0] * f}, ${m.jointMid[1] * f}, ${m.jointMid[2] * f}, ${alpha})`);
        sphereGrad.addColorStop(0.8, `rgba(${m.jointShadow[0] * f}, ${m.jointShadow[1] * f}, ${m.jointShadow[2] * f}, ${alpha})`);
        sphereGrad.addColorStop(1, `rgba(${m.jointShadow[0] * f * 0.4}, ${m.jointShadow[1] * f * 0.4}, ${m.jointShadow[2] * f * 0.4}, ${alpha})`);
        
        ctx.beginPath();
        ctx.arc(x, y, radius, 0, Math.PI * 2);
        ctx.fillStyle = sphereGrad;
        ctx.fill();
        
        // ===================
        // LAYER 3: Sharp specular highlight (white dot, top-left)
        // ===================
        if (depthFactor > 0.25) {
            const specGrad = ctx.createRadialGradient(
                x - radius * 0.35, y - radius * 0.35, 0,
                x - radius * 0.35, y - radius * 0.35, radius * 0.4
            );
            specGrad.addColorStop(0, `rgba(255, 255, 255, ${alpha * 0.65 * f})`);
            specGrad.addColorStop(0.4, `rgba(180, 195, 215, ${alpha * 0.3 * f})`);
            specGrad.addColorStop(1, 'rgba(150, 165, 185, 0)');
            
            ctx.beginPath();
            ctx.arc(x - radius * 0.35, y - radius * 0.35, radius * 0.4, 0, Math.PI * 2);
            ctx.fillStyle = specGrad;
            ctx.fill();
        }
    }

    // ============================================
    // MAIN RENDER FUNCTION
    // ============================================
    function render() {
        // ===================
        // BACKGROUND: Warm dark gradient (NOT pure black)
        // ===================
        const bgGrad = ctx.createLinearGradient(0, 0, 0, height);
        bgGrad.addColorStop(0, '#0C0E12');     // Dark blue-grey top
        bgGrad.addColorStop(0.4, '#090B0E');   // Very dark mid
        bgGrad.addColorStop(1, '#100E0A');     // Warm dark bottom (amber tint)
        ctx.fillStyle = bgGrad;
        ctx.fillRect(0, 0, width, height);
        
        // ===================
        // WARM AMBIENT GLOW (Bottom - like furnace/sunset horizon)
        // This creates the amber reflection seen in reference
        // ===================
        const warmGlow = ctx.createRadialGradient(
            width * 0.5, height * 1.3, 0,
            width * 0.5, height * 0.7, height * 1.0
        );
        warmGlow.addColorStop(0, 'rgba(220, 150, 60, 0.4)');
        warmGlow.addColorStop(0.2, 'rgba(200, 120, 45, 0.25)');
        warmGlow.addColorStop(0.5, 'rgba(150, 80, 30, 0.12)');
        warmGlow.addColorStop(0.8, 'rgba(80, 45, 15, 0.05)');
        warmGlow.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = warmGlow;
        ctx.fillRect(0, 0, width, height);
        
        // Secondary warm glow (left side)
        const warmGlow2 = ctx.createRadialGradient(
            width * 0.2, height * 1.1, 0,
            width * 0.4, height * 0.6, height * 0.7
        );
        warmGlow2.addColorStop(0, 'rgba(200, 140, 50, 0.2)');
        warmGlow2.addColorStop(0.4, 'rgba(150, 90, 30, 0.1)');
        warmGlow2.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = warmGlow2;
        ctx.fillRect(0, 0, width, height);

        // ===================
        // PROJECT ALL NODES
        // ===================
        const projected = nodes.map((node, idx) => ({
            ...project(node),
            idx
        }));

        // ===================
        // SORT CONNECTIONS BY DEPTH (back to front)
        // ===================
        const sortedConns = connections.map(c => {
            const a = projected[c.from];
            const b = projected[c.to];
            return { ...c, a, b, avgZ: (a.z + b.z) / 2 };
        }).sort((a, b) => a.avgZ - b.avgZ);

        // ===================
        // DRAW TUBES (Cylinders - NOT lines)
        // ===================
        for (const conn of sortedConns) {
            const { a, b, avgZ } = conn;
            
            if (a.x < -150 || a.x > width + 150 || b.x < -150 || b.x > width + 150) continue;
            if (a.y < -150 || a.y > height + 150 || b.y < -150 || b.y > height + 150) continue;
            
            const depthFactor = getDepthFactor(avgZ);
            drawCylinder(a.x, a.y, b.x, b.y, depthFactor);
        }

        // ===================
        // SORT NODES BY DEPTH (back to front)
        // ===================
        const sortedNodes = [...projected].sort((a, b) => a.z - b.z);

        // ===================
        // DRAW JOINTS (Dark metallic spheres - NO YELLOW)
        // ===================
        for (const node of sortedNodes) {
            if (node.x < -80 || node.x > width + 80 || node.y < -80 || node.y > height + 80) continue;
            
            const depthFactor = getDepthFactor(node.z);
            drawJoint(node.x, node.y, depthFactor);
        }

        // ===================
        // VIGNETTE (Cinematic depth)
        // ===================
        const vignette = ctx.createRadialGradient(
            width / 2, height / 2, height * 0.2,
            width / 2, height / 2, Math.max(width, height) * 0.8
        );
        vignette.addColorStop(0, 'rgba(0, 0, 0, 0)');
        vignette.addColorStop(0.6, 'rgba(0, 0, 0, 0.1)');
        vignette.addColorStop(1, 'rgba(0, 0, 0, 0.55)');
        ctx.fillStyle = vignette;
        ctx.fillRect(0, 0, width, height);
        
        // ===================
        // TOP FADE (Content readability)
        // ===================
        const topFade = ctx.createLinearGradient(0, 0, 0, height * 0.1);
        topFade.addColorStop(0, 'rgba(12, 14, 18, 0.75)');
        topFade.addColorStop(1, 'rgba(12, 14, 18, 0)');
        ctx.fillStyle = topFade;
        ctx.fillRect(0, 0, width, height * 0.1);
    }

    // ============================================
    // ANIMATION LOOP
    // ============================================
    function animate() {
        time++;
        render();
        requestAnimationFrame(animate);
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
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

    // ============================================
    // START
    // ============================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
