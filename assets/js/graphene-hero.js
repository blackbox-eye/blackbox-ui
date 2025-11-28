import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.158.0/build/three.module.js';

/**
 * Graphene Hero – Three.js macro lattice
 * Fully 3D rendering to match the metallic graphene reference.
 */

const canvas = document.getElementById('graphene-canvas');

if (!canvas) {
    console.warn('Graphene hero canvas element not found.');
} else {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let width = window.innerWidth;
    let height = window.innerHeight;

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setSize(width, height);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.setClearColor(0x050608, 0);

    const scene = new THREE.Scene();
    scene.fog = new THREE.Fog(0x050608, 55, 220);

    const camera = new THREE.PerspectiveCamera(42, width / height, 0.1, 500);
    camera.position.set(0, 34, 78);
    scene.add(camera);

    // Lighting design mimics GreyEYE reference render.
    const warmFloorLight = new THREE.PointLight(0xffa347, 4, 220, 2);
    warmFloorLight.position.set(0, -24, 16);
    scene.add(warmFloorLight);

    const warmAccent = new THREE.PointLight(0xffc068, 1.2, 140);
    warmAccent.position.set(28, -12, -8);
    scene.add(warmAccent);

    const keyLight = new THREE.DirectionalLight(0xffffff, 1.35);
    keyLight.position.set(-36, 58, 24);
    scene.add(keyLight);

    scene.add(new THREE.HemisphereLight(0x1a1f29, 0x040507, 0.55));
    scene.add(new THREE.AmbientLight(0x0c0f16, 0.35));

    const latticeGroup = new THREE.Group();
    scene.add(latticeGroup);
    latticeGroup.rotation.x = -Math.PI / 2.85;
    latticeGroup.position.y = -4;

    const upAxis = new THREE.Vector3(0, 1, 0);
    const tempDir = new THREE.Vector3();
    const tempMid = new THREE.Vector3();

    const tubeGeometry = new THREE.CylinderGeometry(0.35, 0.35, 1, 18, 1, true);
    tubeGeometry.translate(0, 0.5, 0);
    const tubeMaterial = new THREE.MeshStandardMaterial({
        color: 0x1b1f26,
        metalness: 0.92,
        roughness: 0.22,
        envMapIntensity: 1.2
    });

    tubeMaterial.onBeforeCompile = (shader) => {
        shader.fragmentShader = shader.fragmentShader.replace(
            '#include <dithering_fragment>',
            `#include <dithering_fragment>
             float fresnel = pow(1.0 - abs(normal.y), 3.0);
             gl_FragColor.rgb += vec3(0.85, 0.9, 1.0) * fresnel * 0.25;
            `
        );
    };

    const jointGeometry = new THREE.SphereGeometry(0.55, 28, 28);
    const jointMaterial = new THREE.MeshStandardMaterial({
        color: 0x11151c,
        metalness: 0.88,
        roughness: 0.24,
        emissive: 0x050607,
        emissiveIntensity: 0.35
    });

    const nodes = [];
    const connections = [];

    buildLattice();
    createMeshes();
    canvas.classList.add('is-visible');

    const parallax = { targetY: 0, targetTilt: latticeGroup.rotation.x, currentY: 0, currentTilt: latticeGroup.rotation.x };

    function buildLattice() {
        const spacing = 6.6;
        const hexWidth = spacing * 1.5;
        const hexHeight = Math.sqrt(3) * spacing;
        const cols = 16;
        const rows = 9;
        const nodeMap = new Map();

        const offsetX = -((cols - 1) * hexWidth) / 2;
        const offsetZ = -((rows - 1) * hexHeight * 0.75) / 2;

        const getNodeId = (x, z) => {
            const key = `${Math.round(x * 100)}|${Math.round(z * 100)}`;
            if (!nodeMap.has(key)) {
                nodeMap.set(key, nodes.length);
                const baseY = (Math.random() - 0.5) * 1.2;
                nodes.push({
                    basePosition: new THREE.Vector3(x, baseY, z),
                    currentPosition: new THREE.Vector3(x, baseY, z),
                    phase: Math.random() * Math.PI * 2,
                    mesh: null
                });
            }
            return nodeMap.get(key);
        };

        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < cols; col++) {
                const cx = col * hexWidth + (row % 2 ? hexWidth / 2 : 0) + offsetX;
                const cz = row * (hexHeight * 0.75) + offsetZ;

                const corners = [];
                for (let i = 0; i < 6; i++) {
                    const angle = (Math.PI / 3) * i;
                    const vx = cx + Math.cos(angle) * spacing;
                    const vz = cz + Math.sin(angle) * spacing;
                    corners.push(getNodeId(vx, vz));
                }

                for (let i = 0; i < 6; i++) {
                    const a = corners[i];
                    const b = corners[(i + 1) % 6];
                    if (a !== b) {
                        const key = a < b ? `${a}-${b}` : `${b}-${a}`;
                        if (!connections.find((conn) => conn.key === key)) {
                            connections.push({ a, b, key, mesh: null });
                        }
                    }
                }
            }
        }
    }

    function createMeshes() {
        nodes.forEach((node) => {
            const sphere = new THREE.Mesh(jointGeometry, jointMaterial);
            sphere.position.copy(node.basePosition);
            latticeGroup.add(sphere);
            node.mesh = sphere;
        });

        connections.forEach((conn) => {
            const tube = new THREE.Mesh(tubeGeometry, tubeMaterial);
            latticeGroup.add(tube);
            conn.mesh = tube;
            updateTube(conn);
        });
    }

    function updateTube(conn) {
        const aPos = nodes[conn.a].currentPosition;
        const bPos = nodes[conn.b].currentPosition;
        tempDir.subVectors(bPos, aPos);
        const length = tempDir.length();
        if (!length) {
            return;
        }

        conn.mesh.scale.set(1, length, 1);
        tempMid.copy(aPos).addScaledVector(tempDir, 0.5);
        conn.mesh.position.copy(tempMid);
        conn.mesh.quaternion.setFromUnitVectors(upAxis, tempDir.clone().normalize());
    }

    function updateNodes(elapsed) {
        nodes.forEach((node) => {
            if (reduceMotion) {
                node.currentPosition.copy(node.basePosition);
            } else {
                const lift = Math.sin(elapsed * 0.45 + node.phase) * 0.4;
                node.currentPosition.copy(node.basePosition);
                node.currentPosition.y += lift;
            }
            node.mesh.position.copy(node.currentPosition);
        });

        connections.forEach(updateTube);
    }

    function handleResize() {
        width = window.innerWidth;
        height = window.innerHeight;
        renderer.setSize(width, height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    }

    function onPointerMove(event) {
        const ratioX = (event.clientX / width) - 0.5;
        const ratioY = (event.clientY / height) - 0.5;
        parallax.targetY = ratioX * 0.3;
        parallax.targetTilt = (-Math.PI / 2.85) + ratioY * 0.08;
    }

    window.addEventListener('resize', handleResize);
    window.addEventListener('pointermove', onPointerMove);

    let rafId;
    function animate() {
        const elapsed = performance.now() * 0.001;

        parallax.currentY += (parallax.targetY - parallax.currentY) * 0.05;
        parallax.currentTilt += (parallax.targetTilt - parallax.currentTilt) * 0.05;

        latticeGroup.rotation.y = parallax.currentY;
        latticeGroup.rotation.x = parallax.currentTilt;
        latticeGroup.position.y = -4 + Math.sin(elapsed * 0.25) * 1.2;

        updateNodes(elapsed);
        renderer.render(scene, camera);

        if (!reduceMotion) {
            rafId = requestAnimationFrame(animate);
        }
    }

    if (reduceMotion) {
        updateNodes(0);
        renderer.render(scene, camera);
    } else {
        animate();
    }

    window.addEventListener('beforeunload', () => {
        if (rafId) {
            cancelAnimationFrame(rafId);
        }
        renderer.dispose();
        tubeGeometry.dispose();
        jointGeometry.dispose();
    });
}
