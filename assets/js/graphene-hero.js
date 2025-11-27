/**
 * Graphene Hero - 3D Hexagon Network Animation
 * BlackboxEYE × GreyEYE Fusion Design
 * 
 * Creates an interactive hexagonal mesh with glowing nodes,
 * particle effects, and mouse-reactive animations.
 */

(function() {
    'use strict';

    const canvas = document.getElementById('graphene-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width, height, dpr;
    let mouseX = 0, mouseY = 0;
    let animationId;
    let nodes = [];
    let connections = [];
    let particles = [];
    let time = 0;

    // Configuration
    const config = {
        // Colors - Gold/Grey Fusion
        goldPrimary: '#D4AF37',
        goldLight: '#F5E6B8',
        goldDark: '#8B7355',
        greyLight: '#4A5568',
        greyDark: '#1A202C',
        nodeGlow: 'rgba(212, 175, 55, 0.6)',
        
        // Hexagon grid
        hexSize: 60,
        hexSpacing: 65,
        
        // Animation
        pulseSpeed: 0.02,
        floatAmplitude: 3,
        mouseInfluence: 150,
        
        // Particles
        particleCount: 50,
        particleSpeed: 0.5,
        
        // Performance
        fps: 60,
        reduceMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches
    };

    // Initialize canvas
    function initCanvas() {
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = window.innerWidth;
        height = window.innerHeight;
        
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        ctx.scale(dpr, dpr);
        
        createHexagonGrid();
        createParticles();
    }

    // Create hexagon grid nodes
    function createHexagonGrid() {
        nodes = [];
        connections = [];
        
        const cols = Math.ceil(width / config.hexSpacing) + 2;
        const rows = Math.ceil(height / (config.hexSpacing * 0.866)) + 2;
        
        for (let row = -1; row < rows; row++) {
            for (let col = -1; col < cols; col++) {
                const x = col * config.hexSpacing + (row % 2) * (config.hexSpacing / 2);
                const y = row * config.hexSpacing * 0.866;
                
                const node = {
                    x: x,
                    y: y,
                    baseX: x,
                    baseY: y,
                    radius: 3 + Math.random() * 3,
                    phase: Math.random() * Math.PI * 2,
                    pulsePhase: Math.random() * Math.PI * 2,
                    brightness: 0.3 + Math.random() * 0.4,
                    isGold: Math.random() > 0.7, // 30% gold nodes
                    connections: []
                };
                
                nodes.push(node);
            }
        }
        
        // Create connections between nearby nodes
        for (let i = 0; i < nodes.length; i++) {
            for (let j = i + 1; j < nodes.length; j++) {
                const dx = nodes[i].baseX - nodes[j].baseX;
                const dy = nodes[i].baseY - nodes[j].baseY;
                const dist = Math.sqrt(dx * dx + dy * dy);
                
                if (dist < config.hexSpacing * 1.2) {
                    connections.push({
                        from: i,
                        to: j,
                        progress: Math.random(),
                        speed: 0.002 + Math.random() * 0.003,
                        active: Math.random() > 0.5
                    });
                    nodes[i].connections.push(j);
                    nodes[j].connections.push(i);
                }
            }
        }
    }

    // Create floating particles
    function createParticles() {
        particles = [];
        
        for (let i = 0; i < config.particleCount; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * config.particleSpeed,
                vy: (Math.random() - 0.5) * config.particleSpeed,
                radius: 1 + Math.random() * 2,
                opacity: 0.2 + Math.random() * 0.5,
                isGold: Math.random() > 0.6
            });
        }
    }

    // Draw hexagon shape
    function drawHexagon(x, y, size, fill, stroke) {
        ctx.beginPath();
        for (let i = 0; i < 6; i++) {
            const angle = (Math.PI / 3) * i - Math.PI / 6;
            const hx = x + size * Math.cos(angle);
            const hy = y + size * Math.sin(angle);
            if (i === 0) ctx.moveTo(hx, hy);
            else ctx.lineTo(hx, hy);
        }
        ctx.closePath();
        
        if (fill) {
            ctx.fillStyle = fill;
            ctx.fill();
        }
        if (stroke) {
            ctx.strokeStyle = stroke;
            ctx.lineWidth = 1;
            ctx.stroke();
        }
    }

    // Update node positions based on mouse and time
    function updateNodes() {
        for (const node of nodes) {
            // Floating animation
            const float = Math.sin(time * config.pulseSpeed + node.phase) * config.floatAmplitude;
            
            // Mouse influence
            const dx = mouseX - node.baseX;
            const dy = mouseY - node.baseY;
            const dist = Math.sqrt(dx * dx + dy * dy);
            
            let pushX = 0, pushY = 0;
            if (dist < config.mouseInfluence && dist > 0) {
                const force = (1 - dist / config.mouseInfluence) * 20;
                pushX = (dx / dist) * force;
                pushY = (dy / dist) * force;
            }
            
            node.x = node.baseX + pushX;
            node.y = node.baseY + float + pushY;
            
            // Pulse brightness
            node.currentBrightness = node.brightness + 
                Math.sin(time * 0.03 + node.pulsePhase) * 0.2;
        }
    }

    // Update particles
    function updateParticles() {
        for (const p of particles) {
            p.x += p.vx;
            p.y += p.vy;
            
            // Wrap around edges
            if (p.x < 0) p.x = width;
            if (p.x > width) p.x = 0;
            if (p.y < 0) p.y = height;
            if (p.y > height) p.y = 0;
            
            // Subtle mouse attraction
            const dx = mouseX - p.x;
            const dy = mouseY - p.y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 200 && dist > 0) {
                p.vx += (dx / dist) * 0.01;
                p.vy += (dy / dist) * 0.01;
            }
            
            // Limit velocity
            const speed = Math.sqrt(p.vx * p.vx + p.vy * p.vy);
            if (speed > config.particleSpeed * 2) {
                p.vx = (p.vx / speed) * config.particleSpeed * 2;
                p.vy = (p.vy / speed) * config.particleSpeed * 2;
            }
        }
    }

    // Update connection animations
    function updateConnections() {
        for (const conn of connections) {
            if (conn.active) {
                conn.progress += conn.speed;
                if (conn.progress > 1) {
                    conn.progress = 0;
                    conn.active = Math.random() > 0.3;
                }
            } else if (Math.random() < 0.001) {
                conn.active = true;
                conn.progress = 0;
            }
        }
    }

    // Render everything
    function render() {
        // Clear with gradient background
        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0, '#0D1114');
        gradient.addColorStop(0.5, '#0A0E11');
        gradient.addColorStop(1, '#050708');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, width, height);

        // Draw connections
        ctx.lineCap = 'round';
        for (const conn of connections) {
            const from = nodes[conn.from];
            const to = nodes[conn.to];
            
            // Base connection line
            const baseOpacity = 0.1 + (from.currentBrightness + to.currentBrightness) * 0.1;
            ctx.strokeStyle = `rgba(74, 85, 104, ${baseOpacity})`;
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(from.x, from.y);
            ctx.lineTo(to.x, to.y);
            ctx.stroke();
            
            // Animated pulse on connection
            if (conn.active) {
                const px = from.x + (to.x - from.x) * conn.progress;
                const py = from.y + (to.y - from.y) * conn.progress;
                
                const pulseGradient = ctx.createRadialGradient(px, py, 0, px, py, 15);
                pulseGradient.addColorStop(0, 'rgba(212, 175, 55, 0.8)');
                pulseGradient.addColorStop(1, 'rgba(212, 175, 55, 0)');
                
                ctx.fillStyle = pulseGradient;
                ctx.beginPath();
                ctx.arc(px, py, 15, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        // Draw particles
        for (const p of particles) {
            const color = p.isGold ? config.goldPrimary : config.greyLight;
            ctx.fillStyle = color.replace(')', `, ${p.opacity})`).replace('rgb', 'rgba');
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fill();
        }

        // Draw nodes with glow
        for (const node of nodes) {
            const brightness = node.currentBrightness;
            
            // Outer glow for gold nodes
            if (node.isGold && brightness > 0.4) {
                const glowSize = node.radius * 4;
                const glow = ctx.createRadialGradient(
                    node.x, node.y, 0,
                    node.x, node.y, glowSize
                );
                glow.addColorStop(0, `rgba(212, 175, 55, ${brightness * 0.5})`);
                glow.addColorStop(0.5, `rgba(212, 175, 55, ${brightness * 0.2})`);
                glow.addColorStop(1, 'rgba(212, 175, 55, 0)');
                
                ctx.fillStyle = glow;
                ctx.beginPath();
                ctx.arc(node.x, node.y, glowSize, 0, Math.PI * 2);
                ctx.fill();
            }
            
            // Node sphere effect
            const nodeGradient = ctx.createRadialGradient(
                node.x - node.radius * 0.3, 
                node.y - node.radius * 0.3, 
                0,
                node.x, node.y, node.radius
            );
            
            if (node.isGold) {
                nodeGradient.addColorStop(0, config.goldLight);
                nodeGradient.addColorStop(0.5, config.goldPrimary);
                nodeGradient.addColorStop(1, config.goldDark);
            } else {
                nodeGradient.addColorStop(0, '#718096');
                nodeGradient.addColorStop(0.5, config.greyLight);
                nodeGradient.addColorStop(1, config.greyDark);
            }
            
            ctx.fillStyle = nodeGradient;
            ctx.beginPath();
            ctx.arc(node.x, node.y, node.radius, 0, Math.PI * 2);
            ctx.fill();
            
            // Highlight reflection
            ctx.fillStyle = `rgba(255, 255, 255, ${brightness * 0.5})`;
            ctx.beginPath();
            ctx.arc(
                node.x - node.radius * 0.3, 
                node.y - node.radius * 0.3, 
                node.radius * 0.3, 
                0, Math.PI * 2
            );
            ctx.fill();
        }

        // Draw subtle hexagon overlay near mouse
        const hexOpacity = 0.05;
        const hexX = Math.round(mouseX / config.hexSpacing) * config.hexSpacing;
        const hexY = Math.round(mouseY / (config.hexSpacing * 0.866)) * config.hexSpacing * 0.866;
        
        drawHexagon(hexX, hexY, config.hexSize, null, `rgba(212, 175, 55, ${hexOpacity + 0.1})`);
        drawHexagon(hexX, hexY, config.hexSize * 0.8, null, `rgba(212, 175, 55, ${hexOpacity})`);
    }

    // Main animation loop
    function animate() {
        if (config.reduceMotion) {
            render();
            return;
        }

        time++;
        updateNodes();
        updateParticles();
        updateConnections();
        render();
        
        animationId = requestAnimationFrame(animate);
    }

    // Event handlers
    function handleMouseMove(e) {
        const rect = canvas.getBoundingClientRect();
        mouseX = e.clientX - rect.left;
        mouseY = e.clientY - rect.top;
    }

    function handleResize() {
        cancelAnimationFrame(animationId);
        initCanvas();
        animate();
    }

    // Initialize
    function init() {
        initCanvas();
        
        // Event listeners
        canvas.addEventListener('mousemove', handleMouseMove);
        window.addEventListener('resize', debounce(handleResize, 200));
        
        // Start animation
        if (!config.reduceMotion) {
            animate();
        } else {
            render();
        }
        
        // Fade in
        canvas.style.opacity = '0';
        canvas.style.transition = 'opacity 1s ease-in';
        setTimeout(() => {
            canvas.style.opacity = '1';
        }, 100);
        
        console.log('✨ Graphene Hero Animation initialized');
    }

    // Utility: Debounce
    function debounce(fn, delay) {
        let timer;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // Start when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
