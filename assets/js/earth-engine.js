/* ============================================================
   EARTH ENGINE V5 — Hosting & Cloud Ecosystem
   Upgrades from V4:
     • Larger Earth (RADIUS 2.6, camera closer)
     • 120 extra random nodes (double V4)
     • 8 satellites (vs 6)
     • Stronger glow + double atmosphere layers
     • 7 Cloud/Hosting nodes (VPS, Hosting, Server, CDN…)
     • Cloudflare-style dense connection network
     • Data streams: faster, more packets
     • 9 orbit rings (vs 5+energy)
     • Azure/AWS style region rings
     • 350 ambient particles (vs 220)
     • Mobile: auto-LOD as before
   ============================================================ */

window.EarthEngine = (function () {
  'use strict';

  /* ── Constants ───────────────────────────────────────────── */
  const RADIUS      = 2.4;   // V5: larger (was 1.9)
  const CITY_RADIUS = RADIUS + 0.012;

  /* 9 primary city nodes */
  const CITIES = [
    { name: 'Vietnam',    lat:  16.0, lng:  108.0, primary: true  },
    { name: 'Singapore',  lat:   1.3, lng:  103.8, primary: false },
    { name: 'Tokyo',      lat:  35.7, lng:  139.7, primary: false },
    { name: 'Seoul',      lat:  37.6, lng:  126.9, primary: false },
    { name: 'Sydney',     lat: -33.9, lng:  151.2, primary: false },
    { name: 'Frankfurt',  lat:  50.1, lng:    8.7, primary: false },
    { name: 'London',     lat:  51.5, lng:   -0.1, primary: false },
    { name: 'New York',   lat:  40.7, lng:  -74.0, primary: false },
    { name: 'California', lat:  37.8, lng: -122.4, primary: false },
  ];

  /* Hosting/Cloud node positions (lat, lng) */
  const CLOUD_NODES = [
    { lat:  1.4, lng: 103.9, type: 'vps'     }, // SG VPS
    { lat: 35.6, lng: 139.6, type: 'hosting' }, // TYO Hosting
    { lat: 40.7, lng: -74.1, type: 'cdn'     }, // NY CDN
    { lat: 50.0, lng:   8.5, type: 'server'  }, // EU Server
    { lat: 37.7, lng:-122.5, type: 'cloud'   }, // CA Cloud
    { lat: 19.0, lng:  73.0, type: 'hosting' }, // Mumbai
    { lat:-23.5, lng: -46.6, type: 'vps'     }, // Sao Paulo
  ];

  const CONNECTIONS = [
    [0,1],[0,2],[0,3],[0,5],[0,6],[0,7],[0,8],
    [1,2],[1,3],[1,4],[2,3],[3,5],[5,6],[5,7],
    [6,7],[7,8],[1,5],[2,7],[4,7],[0,4],
  ];

  /* ── Modes ────────────────────────────────────────────────── */
  const MODES = {
    NETWORK:  {
      wireOp: 0.12, gridOp: 0.09, nodeOp: 1.0,
      glowColor: 0x6366F1, glowOp: 0.06,
      auroraColor: 0x4F46E5, auroraOp: 0.04,
      connOp: 0.45, satOp: 1.0,
    },
    WIREFRAME:{
      wireOp: 0.50, gridOp: 0.22, nodeOp: 0.35,
      glowColor: 0x818CF8, glowOp: 0.025,
      auroraColor: 0x6366F1, auroraOp: 0.02,
      connOp: 0.18, satOp: 0.7,
    },
    DATA:     {
      wireOp: 0.12, gridOp: 0.10, nodeOp: 1.1,
      glowColor: 0xF59E0B, glowOp: 0.065,
      auroraColor: 0xD97706, auroraOp: 0.04,
      connOp: 0.60, satOp: 1.1,
    },
    ENERGY:   {
      wireOp: 0.07, gridOp: 0.06, nodeOp: 1.4,
      glowColor: 0x10B981, glowOp: 0.09,
      auroraColor: 0x059669, auroraOp: 0.055,
      connOp: 0.75, satOp: 1.3,
    },
    HOLOGRAM: {
      wireOp: 0.28, gridOp: 0.18, nodeOp: 1.0,
      glowColor: 0x22D3EE, glowOp: 0.07,
      auroraColor: 0x0E7490, auroraOp: 0.045,
      connOp: 0.50, satOp: 0.9,
    },
  };

  /* ── Utility ─────────────────────────────────────────────── */
  function latLngToVec3(lat, lng, r) {
    const phi   = (90 - lat) * Math.PI / 180;
    const theta = (lng + 180) * Math.PI / 180;
    return new THREE.Vector3(
      -r * Math.sin(phi) * Math.cos(theta),
       r * Math.cos(phi),
       r * Math.sin(phi) * Math.sin(theta)
    );
  }
  function hexToNum(h) { return parseInt(String(h).replace('#',''), 16); }
  function lerp(a, b, t) { return a + (b - a) * t; }
  function lerpColor(a, b, t) {
    const ar=(a>>16)&0xFF, ag=(a>>8)&0xFF, ab=a&0xFF;
    const br=(b>>16)&0xFF, bg=(b>>8)&0xFF, bb=b&0xFF;
    return (Math.round(ar+(br-ar)*t)<<16)|(Math.round(ag+(bg-ag)*t)<<8)|Math.round(ab+(bb-ab)*t);
  }

  /* ── Main init ───────────────────────────────────────────── */
  function init(canvasId) {
    if (typeof THREE === 'undefined') return null;
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;

    const isMobile = window.innerWidth < 768;
    const isLowEnd = isMobile && (navigator.hardwareConcurrency || 4) <= 2;

    const Q = {
      segments:   isLowEnd ? 20 : isMobile ? 28 : 56,
      gridSegs:   isLowEnd ? 50 : isMobile ? 72 : 120,
      particles:  isLowEnd ? 80 : isMobile ? 150 : 350,
      satellites: isLowEnd ? 2  : isMobile ? 3   : 8,
      extraNodes: isLowEnd ? 30 : isMobile ? 60  : 120,
      antialias:  !isMobile,
      maxFPS:     isMobile ? 30 : 60,
    };

    /* Scene */
    const scene  = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(36, 1, 0.1, 1000);
    camera.position.z = isMobile ? 6.5 : 5.4; // closer = bigger earth feel

    const renderer = new THREE.WebGLRenderer({
      canvas, alpha: true, antialias: Q.antialias,
      powerPreference: 'high-performance',
    });
    renderer.setClearColor(0x000000, 0);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, isMobile ? 1.5 : 2));

    /* Earth group */
    const earthGroup = new THREE.Group();
    scene.add(earthGroup);

    /* Accent colors */
    const cs = getComputedStyle(document.documentElement);
    const accentNum  = hexToNum((cs.getPropertyValue('--accent')  ||'#6366F1').trim());
    const accent2Num = hexToNum((cs.getPropertyValue('--accent-2')||'#8B5CF6').trim());

    /* ─── LAYER 1: Core Earth ────────────────────────────── */
    earthGroup.add(new THREE.Mesh(
      new THREE.SphereGeometry(RADIUS - 0.02, Q.segments, Q.segments),
      new THREE.MeshBasicMaterial({ color: 0x01010A, transparent:true, opacity:0.98 })
    ));

    /* ─── LAYER 2: Wireframe ─────────────────────────────── */
    const wireMat = new THREE.MeshBasicMaterial({
      color: accentNum, wireframe:true, transparent:true, opacity:0.12
    });
    earthGroup.add(new THREE.Mesh(
      new THREE.SphereGeometry(RADIUS, Q.segments, Q.segments), wireMat
    ));

    /* ─── LAYER 3: Digital Grid ──────────────────────────── */
    const gridGroup = new THREE.Group();
    const gMat = () => new THREE.LineBasicMaterial({ color: accentNum, transparent:true, opacity:0.09 });
    function latLine(lat) {
      const phi = (90-lat)*Math.PI/180, r=RADIUS*Math.sin(phi), y=RADIUS*Math.cos(phi);
      const pts=[];
      for(let i=0;i<=Q.gridSegs;i++){const t=i/Q.gridSegs*Math.PI*2; pts.push(new THREE.Vector3(r*Math.cos(t),y,r*Math.sin(t)));}
      gridGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts),gMat()));
    }
    function lngLine(lng) {
      const t=lng*Math.PI/180;
      const pts=[];
      for(let i=0;i<=Q.gridSegs;i++){const phi=i/Q.gridSegs*Math.PI; pts.push(new THREE.Vector3(RADIUS*Math.sin(phi)*Math.cos(t),RADIUS*Math.cos(phi),RADIUS*Math.sin(phi)*Math.sin(t)));}
      gridGroup.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts),gMat()));
    }
    [-75,-60,-45,-30,-15,0,15,30,45,60,75].forEach(latLine);
    [0,20,40,60,80,100,120,140,160,180,200,220,240,260,280,300,320,340].forEach(lngLine);
    earthGroup.add(gridGroup);

    /* ─── LAYER 4: Dual Atmosphere ───────────────────────── */
    const glowMat = new THREE.MeshBasicMaterial({
      color: accentNum, transparent:true, opacity:0.06, side:THREE.FrontSide
    });
    const glowMesh = new THREE.Mesh(new THREE.SphereGeometry(RADIUS+0.08, 32,32), glowMat);
    const glow2Mat = new THREE.MeshBasicMaterial({
      color: 0x3B82F6, transparent:true, opacity:0.025, side:THREE.FrontSide
    });
    const glowMesh2 = new THREE.Mesh(new THREE.SphereGeometry(RADIUS+0.22, 32,32), glow2Mat);
    const auroraMat = new THREE.MeshBasicMaterial({
      color: 0x4F46E5, transparent:true, opacity:0.04, side:THREE.BackSide
    });
    const auroraMesh = new THREE.Mesh(new THREE.SphereGeometry(RADIUS+0.50, 32,32), auroraMat);
    const aurora2Mat = new THREE.MeshBasicMaterial({
      color: 0x7C3AED, transparent:true, opacity:0.02, side:THREE.BackSide
    });
    const auroraMesh2 = new THREE.Mesh(new THREE.SphereGeometry(RADIUS+0.90, 32,32), aurora2Mat);
    [glowMesh, glowMesh2, auroraMesh, auroraMesh2].forEach(m => earthGroup.add(m));

    /* ─── LAYER 5: 9 Orbit Rings ─────────────────────────── */
    function mkRing(r, rx, ry, rz, op, color, tube=0.004) {
      const m = new THREE.Mesh(
        new THREE.TorusGeometry(r, tube, 4, 160),
        new THREE.MeshBasicMaterial({ color:color||accentNum, transparent:true, opacity:op })
      );
      m.rotation.set(rx,ry,rz);
      return m;
    }
    const rings = [
      mkRing(2.75,  Math.PI/5,  0,  0,   0.30),
      mkRing(2.95, -Math.PI/6,  0,  Math.PI/4, 0.22),
      mkRing(2.55,  Math.PI/2,  0,  0,   0.18),
      mkRing(3.20,  Math.PI/3,  0,  Math.PI/3, 0.13),
      mkRing(3.55,  Math.PI/7,  Math.PI/5, 0, 0.08, 0x3B82F6),
      mkRing(2.85,  Math.PI/8, -Math.PI/4, 0, 0.16, 0x10B981),
      mkRing(3.80,  Math.PI/4,  Math.PI/3, 0, 0.05, 0xF59E0B, 0.003),
      mkRing(4.20,  0.3,        0.7,       0, 0.04, 0x22D3EE, 0.003),
    ];
    rings.forEach(r => earthGroup.add(r));

    // Equatorial energy ring
    const eqRing = new THREE.Mesh(
      new THREE.TorusGeometry(RADIUS+0.65, 0.003, 4, 220),
      new THREE.MeshBasicMaterial({ color:0x818CF8, transparent:true, opacity:0.28 })
    );
    eqRing.rotation.x = Math.PI/2;
    earthGroup.add(eqRing);

    /* ─── LAYER 6: Data Network ──────────────────────────── */
    const networkGroup = new THREE.Group();

    const cityPos3D = CITIES.map(c => ({...c, pos:latLngToVec3(c.lat,c.lng,CITY_RADIUS)}));

    const cityDots=[], pulseRings=[];
    cityPos3D.forEach(city => {
      const color = city.primary ? 0x10B981 : accentNum;
      const size  = city.primary ? 0.052 : 0.033;
      const dot = new THREE.Mesh(new THREE.SphereGeometry(size,8,8), new THREE.MeshBasicMaterial({color}));
      dot.position.copy(city.pos);
      cityDots.push(dot);
      networkGroup.add(dot);

      const pr = new THREE.Mesh(
        new THREE.TorusGeometry(city.primary?0.10:0.065, 0.004, 4, 32),
        new THREE.MeshBasicMaterial({color, transparent:true, opacity:0.6})
      );
      pr.position.copy(city.pos);
      pr.lookAt(new THREE.Vector3(0,0,0));
      pr.userData.phase = Math.random()*Math.PI*2;
      pulseRings.push(pr);
      networkGroup.add(pr);
    });

    /* Cloud/Hosting nodes */
    const cloudNodeColors = {vps:0x3B82F6, hosting:0xF59E0B, cdn:0x22D3EE, server:0x10B981, cloud:0xA78BFA};
    const cloudDots=[], cloudPulse=[];
    CLOUD_NODES.forEach(cn => {
      const pos   = latLngToVec3(cn.lat, cn.lng, CITY_RADIUS+0.008);
      const color = cloudNodeColors[cn.type] || 0x818CF8;
      const dot   = new THREE.Mesh(new THREE.SphereGeometry(0.038,8,8), new THREE.MeshBasicMaterial({color, transparent:true, opacity:0.85}));
      dot.position.copy(pos);
      cloudDots.push(dot);
      networkGroup.add(dot);

      const cp = new THREE.Mesh(
        new THREE.TorusGeometry(0.055, 0.003, 4, 24),
        new THREE.MeshBasicMaterial({color, transparent:true, opacity:0.5})
      );
      cp.position.copy(pos); cp.lookAt(new THREE.Vector3(0,0,0));
      cp.userData.phase = Math.random()*Math.PI*2;
      cloudPulse.push(cp);
      networkGroup.add(cp);
    });

    /* Arc connections */
    const arcLines=[], dataPackets=[];
    CONNECTIONS.forEach(([a,b], idx) => {
      const pA=cityPos3D[a].pos, pB=cityPos3D[b].pos;
      const pts=[];
      const N=50;
      for(let i=0;i<=N;i++){
        const t=i/N;
        pts.push(new THREE.Vector3().lerpVectors(pA,pB,t).normalize().multiplyScalar(CITY_RADIUS+0.02));
      }
      const arcMat = new THREE.LineBasicMaterial({color:accentNum, transparent:true, opacity:0.0});
      const line = new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts), arcMat);
      line.userData.phase = idx*0.38;
      arcLines.push(line);
      networkGroup.add(line);

      // Multiple data packets per arc
      for(let pk=0; pk<(idx<5?2:1); pk++) {
        const packet = new THREE.Mesh(
          new THREE.SphereGeometry(0.022,5,5),
          new THREE.MeshBasicMaterial({color:pk===0?0xffffff:0xA78BFA, transparent:true, opacity:0.0})
        );
        packet.userData.arcPts  = pts;
        packet.userData.progress= Math.random();
        packet.userData.speed   = 0.004 + Math.random()*0.004;
        dataPackets.push(packet);
        networkGroup.add(packet);
      }
    });

    /* Extra random surface nodes — 120 on desktop */
    for(let i=0; i<Q.extraNodes; i++) {
      const phi=Math.acos(2*Math.random()-1), theta=2*Math.PI*Math.random();
      const node = new THREE.Mesh(
        new THREE.SphereGeometry(Math.random()*0.015+0.005, 5,5),
        new THREE.MeshBasicMaterial({
          color: [accentNum,accent2Num,0x3B82F6,0xF59E0B,0x10B981][Math.floor(Math.random()*5)],
          transparent:true, opacity:0.4+Math.random()*0.4
        })
      );
      node.position.set(
        CITY_RADIUS*Math.sin(phi)*Math.cos(theta),
        CITY_RADIUS*Math.sin(phi)*Math.sin(theta),
        CITY_RADIUS*Math.cos(phi)
      );
      networkGroup.add(node);
    }
    earthGroup.add(networkGroup);

    /* ─── LAYER 7: 8 Satellites ──────────────────────────── */
    const SAT_CFGS = [
      { r:3.0,  speed:0.011, tiltX: 0.42, tiltZ: 0.00, color:0xffffff },
      { r:3.25, speed:0.008, tiltX:-0.50, tiltZ: 0.80, color:accent2Num },
      { r:2.75, speed:0.017, tiltX: 1.57, tiltZ: 0.30, color:0x3B82F6 },
      { r:3.60, speed:0.006, tiltX: 0.90, tiltZ:-0.50, color:0xF59E0B },
      { r:3.15, speed:0.013, tiltX:-0.25, tiltZ: 1.10, color:0x10B981 },
      { r:4.00, speed:0.005, tiltX: 0.60, tiltZ: 0.70, color:0x22D3EE },
      { r:2.90, speed:0.015, tiltX: 1.20, tiltZ:-0.30, color:0xFBBF24 },
      { r:4.50, speed:0.004, tiltX: 0.80, tiltZ: 1.40, color:0xA78BFA },
    ].slice(0, Q.satellites);

    const satellites = SAT_CFGS.map(cfg => {
      const body = new THREE.Mesh(
        new THREE.SphereGeometry(0.028, 6, 6),
        new THREE.MeshBasicMaterial({color:cfg.color})
      );
      const TRAIL=35;
      const trailPts = Array.from({length:TRAIL}, ()=>new THREE.Vector3());
      const trail = new THREE.Line(
        new THREE.BufferGeometry().setFromPoints(trailPts),
        new THREE.LineBasicMaterial({color:cfg.color, transparent:true, opacity:0.28})
      );
      scene.add(body); scene.add(trail);
      return { body, trail, trailPts, ...cfg, angle:Math.random()*Math.PI*2 };
    });

    /* ─── LAYER 8: Dense Particles ───────────────────────── */
    const pPos = new Float32Array(Q.particles*3);
    for(let i=0;i<Q.particles;i++){
      pPos[i*3]  =(Math.random()-0.5)*12;
      pPos[i*3+1]=(Math.random()-0.5)*12;
      pPos[i*3+2]=(Math.random()-0.5)*12;
    }
    const pGeo = new THREE.BufferGeometry();
    pGeo.setAttribute('position', new THREE.BufferAttribute(pPos,3));
    const particlesMesh = new THREE.Points(pGeo,
      new THREE.PointsMaterial({color:accentNum, size:0.018, transparent:true, opacity:0.28})
    );
    scene.add(particlesMesh);

    /* ─── Size & Resize ──────────────────────────────────── */
    function resize() {
      const W=window.innerWidth, H=window.innerHeight;
      camera.aspect=W/H; camera.updateProjectionMatrix(); renderer.setSize(W,H);
    }
    resize();
    window.addEventListener('resize', resize, {passive:true});

    /* ─── Interactive Drag + Momentum ────────────────────── */
    let isDragging=false, prevMouseX=0, prevMouseY=0, velocityX=0, velocityY=0;
    const FRICTION=0.91, AUTO_SPEED=0.0015, DRAG_SCALE=0.005;

    function startDrag(x,y){isDragging=true;prevMouseX=x;prevMouseY=y;velocityX=0;velocityY=0;}
    function moveDrag(x,y){
      if(!isDragging)return;
      const dx=x-prevMouseX, dy=y-prevMouseY;
      velocityX=dx*DRAG_SCALE; velocityY=dy*DRAG_SCALE;
      earthGroup.rotation.y+=velocityX;
      earthGroup.rotation.x=Math.max(-0.65,Math.min(0.65,earthGroup.rotation.x+velocityY));
      prevMouseX=x; prevMouseY=y;
    }
    function endDrag(){isDragging=false;}

    canvas.addEventListener('mousedown', e=>{e.preventDefault();startDrag(e.clientX,e.clientY);},{passive:false});
    window.addEventListener('mousemove', e=>moveDrag(e.clientX,e.clientY),{passive:true});
    window.addEventListener('mouseup', endDrag,{passive:true});
    canvas.addEventListener('touchstart',e=>{if(e.touches.length===1){e.preventDefault();startDrag(e.touches[0].clientX,e.touches[0].clientY);}},{passive:false});
    canvas.addEventListener('touchmove', e=>{if(e.touches.length===1){e.preventDefault();moveDrag(e.touches[0].clientX,e.touches[0].clientY);}},{passive:false});
    canvas.addEventListener('touchend',endDrag,{passive:true});
    canvas.addEventListener('wheel',e=>{camera.position.z=Math.max(3.5,Math.min(8.0,camera.position.z+e.deltaY*0.003));},{passive:true});

    /* ─── Scroll Parallax + Morphing ────────────────────── */
    const SECTION_MODES=['NETWORK','WIREFRAME','DATA','ENERGY','HOLOGRAM'];
    let currentMode='NETWORK', targetMode='NETWORK', morphT=1.0;
    const MORPH_SPEED=0.025;

    const sectionModeMap={};
    document.querySelectorAll('section[id]').forEach((sec,idx)=>{sectionModeMap[sec.id]=SECTION_MODES[idx%SECTION_MODES.length];});
    Object.assign(sectionModeMap,{
      'hero':          'NETWORK',
      'sec-websites':  'DATA',
      'sec-global-map':'NETWORK',
      'sec-about':     'WIREFRAME',
      'sec-skills':    'DATA',
      'sec-statistics':'ENERGY',
      'sec-services':  'DATA',
      'sec-payment':   'ENERGY',
      'sec-contact':   'HOLOGRAM',
    });

    let scrollProgress=0;
    const BASE_OFFSET_X = isMobile ? 0 : 1.9;   // V5: larger offset = more off-screen
    const BASE_OFFSET_Y = isMobile ? -0.6 : -0.2;

    function onScroll(){
      scrollProgress=window.scrollY/Math.max(1,document.documentElement.scrollHeight-window.innerHeight);
      let active='hero';
      document.querySelectorAll('section[id]').forEach(sec=>{
        if(sec.getBoundingClientRect().top<=window.innerHeight*0.4)active=sec.id;
      });
      const newMode=sectionModeMap[active]||'NETWORK';
      if(newMode!==targetMode){currentMode=targetMode;targetMode=newMode;morphT=0;}
    }
    window.addEventListener('scroll',onScroll,{passive:true});

    function getModeVal(key){
      const A=MODES[currentMode],B=MODES[targetMode],t=morphT;
      if(typeof A[key]==='number'&&key.endsWith('Op'))return lerp(A[key],B[key],t);
      if(key.endsWith('Color'))return lerpColor(A[key],B[key],t);
      return t<0.5?A[key]:B[key];
    }

    /* ─── Animation Loop ─────────────────────────────────── */
    let frame=0, lastTime=0;
    const FRAME_MS=1000/Q.maxFPS;

    function animate(time){
      requestAnimationFrame(animate);
      if(time-lastTime<FRAME_MS)return;
      lastTime=time; frame++;

      if(morphT<1)morphT=Math.min(1,morphT+MORPH_SPEED);
      window._earthCurrentMode=targetMode;

      const wireOp=getModeVal('wireOp'), gridOp=getModeVal('gridOp'), nodeOp=getModeVal('nodeOp');
      const glowColor=getModeVal('glowColor'), glowOp=getModeVal('glowOp');
      const auroraColor=getModeVal('auroraColor'), auroraOp=getModeVal('auroraOp');
      const connOp=getModeVal('connOp'), satOp=getModeVal('satOp');

      wireMat.opacity = wireOp;
      glowMat.color.setHex(glowColor);
      glowMat.opacity = glowOp + 0.02*Math.sin(frame*0.018);
      glow2Mat.opacity = (glowOp*0.4) + 0.01*Math.sin(frame*0.025+1);
      auroraMat.color.setHex(auroraColor);
      auroraMat.opacity = auroraOp + 0.015*Math.sin(frame*0.013+1);
      aurora2Mat.opacity = (auroraOp*0.5) + 0.008*Math.sin(frame*0.009+2);

      gridGroup.children.forEach(l=>{l.material.opacity=gridOp;});

      /* Momentum + auto-rotate */
      if(!isDragging){
        earthGroup.rotation.y+=velocityX+AUTO_SPEED;
        earthGroup.rotation.x=Math.max(-0.65,Math.min(0.65,earthGroup.rotation.x+velocityY));
        velocityX*=FRICTION; velocityY*=FRICTION;
      }

      /* Parallax */
      const tX=BASE_OFFSET_X-scrollProgress*(isMobile?0:0.7);
      const tY=BASE_OFFSET_Y-scrollProgress*(isMobile?0.4:1.5);
      earthGroup.position.x+=(tX-earthGroup.position.x)*0.04;
      earthGroup.position.y+=(tY-earthGroup.position.y)*0.04;

      /* Orbit rings */
      const rSpeeds=[0.004,-0.003,0.0025,-0.002,0.0015,-0.0018,0.001,-0.0008];
      rings.forEach((r,i)=>{r.rotation.z+=rSpeeds[i]||0.001;});
      eqRing.material.opacity=0.18+0.14*Math.sin(frame*0.022);

      /* Pulse rings */
      pulseRings.forEach(pr=>{
        const s=1+0.40*Math.abs(Math.sin(frame*0.032+pr.userData.phase));
        pr.scale.set(s,s,s); pr.material.opacity=nodeOp*0.6*(1-(s-1)/0.40);
      });
      cloudPulse.forEach(cp=>{
        const s=1+0.30*Math.abs(Math.sin(frame*0.028+cp.userData.phase));
        cp.scale.set(s,s,s); cp.material.opacity=0.5*(1-(s-1)/0.30);
      });

      /* Arc lines + packets */
      arcLines.forEach((line,i)=>{
        const t=(Math.sin(frame*0.022+line.userData.phase)+1)/2;
        line.material.opacity=connOp*t*0.75;
      });
      dataPackets.forEach(pk=>{
        pk.userData.progress+=pk.userData.speed;
        if(pk.userData.progress>1)pk.userData.progress=0;
        const pts=pk.userData.arcPts;
        const fi=pk.userData.progress*(pts.length-1);
        const i0=Math.floor(fi), i1=Math.min(i0+1,pts.length-1);
        pk.position.lerpVectors(pts[i0],pts[i1],fi-i0);
        pk.material.opacity=connOp*0.8*Math.sin(pk.userData.progress*Math.PI);
      });

      cityDots.forEach(d=>{d.material.opacity=Math.max(0.35,nodeOp);});
      cloudDots.forEach(d=>{d.material.opacity=0.7+0.3*Math.sin(frame*0.04+Math.random()*0.01);});

      /* Satellites */
      satellites.forEach(sat=>{
        sat.angle+=sat.speed;
        const x=sat.r*Math.cos(sat.angle), z=sat.r*Math.sin(sat.angle);
        const cosX=Math.cos(sat.tiltX),sinX=Math.sin(sat.tiltX);
        const cosZ=Math.cos(sat.tiltZ),sinZ=Math.sin(sat.tiltZ);
        const xr=x*cosZ;
        const yr=x*sinZ*sinX+z*cosX;
        const zr=-x*sinZ*cosX+z*sinX;
        sat.body.position.set(xr,yr,zr);
        sat.body.material.opacity=Math.min(1,satOp);
        const TL=sat.trailPts.length;
        for(let j=TL-1;j>0;j--)sat.trailPts[j].copy(sat.trailPts[j-1]);
        sat.trailPts[0].copy(sat.body.position);
        sat.trail.geometry.setFromPoints(sat.trailPts);
        sat.trail.material.opacity=Math.min(0.28,satOp*0.28);
      });

      /* Particles */
      particlesMesh.rotation.y-=0.0004;
      particlesMesh.rotation.x+=0.0002;

      renderer.render(scene, camera);
    }

    requestAnimationFrame(animate);
    return { scene, camera, renderer, earthGroup };
  }

  return { init };
})();
